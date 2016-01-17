#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <mysql.h>
#include <stdbool.h>
#include <errno.h>
#include <limits.h>

#define EXIT_FAILURE 1
#define BUFFER_SIZE 150
#define DESC_BUFFER_SIZE 100
#define ID_SIZE_MAX 10
#define AMOUNT_SIZE_MAX 24
#define TAN_SIZE 16
#define MAX_TANS 99
#define MAX_TRANSFERS 100
#define NUM_BATCH_ARGS 3
#define MYSQL_SERVER_ADDRESS "localhost"
#define MYSQL_USER "root"
#define MYSQL_PW "?team13!"
#define MYSQL_DB "bank"

struct CmdArguments
{
    int sender_id;
    int tan_id;
    char tan[TAN_SIZE];
};

struct BatchArguments
{
    char* start;
    char* end;
};

struct BatchTransaction
{
    int receiver_id;
    float amount;
    char description[DESC_BUFFER_SIZE];
};

struct Pair
{
    char start;
    char end;
};

#define NUM_CHAR_RANGES 3
struct Pair allowedCharRange[] = { (struct Pair) { 48, 57 }, (struct Pair) { 65, 90 }, (struct Pair) { 97, 122 } };
struct Pair batchSeperation = (struct Pair) { '<', '>' };
int maxBatchArgSize[] = { ID_SIZE_MAX, AMOUNT_SIZE_MAX, DESC_BUFFER_SIZE };

void usage(char **argv);
struct CmdArguments ParseCmdArguments(MYSQL* conn, const char* sender, const char* tan_id, const char* tan, const char* fileName);
bool ConvertID(MYSQL *conn, const char* ID_Raw, int* ID_checked, bool batchArgument);
bool CheckID(MYSQL *conn, const int ID);
bool ConvertInt(const char* rawInt, int* Int_checked, bool batchArgument);
bool CheckDBOccurence(MYSQL *conn, const char* query);
bool CheckTAN(const char* rawTan);
bool CheckAllowedChar(const char c, const bool description);

FILE* OpenFile(const char* filePath);

bool ProcessTransaction(MYSQL* conn, struct BatchTransaction* transaction, const char* line, int sender_id);
bool ParseRawBatchFile(const char* lineBuffer, struct BatchArguments *args);
bool FindBatchArgument(struct BatchArguments *args, const char* argStart, const char* lineStart, int maxSize);
bool CheckBatchArguments(MYSQL* conn, struct BatchArguments *argPositions, struct BatchTransaction* transaction, int sender_id);
bool ConvertFloat(const char* rawFloat, float* float_Checked, bool batchArgument);
bool CheckSenderBalance(MYSQL* conn, int sender_id, float amount);
bool CheckDescription(struct BatchArguments raw);

bool PerformTransaction(MYSQL* conn, struct BatchTransaction* transaction, int sender_id);

int main(int argc, char **argv) {
    FILE *batch_file;
    char line_buffer[BUFFER_SIZE];

    if(argc != 5)
    {
        usage(argv);
    }

    MYSQL *conn;
    char *server = MYSQL_SERVER_ADDRESS;
    char *user = MYSQL_USER;
    char *password = MYSQL_PW;
    char *database = MYSQL_DB;
    conn = mysql_init(NULL);

    if (!mysql_real_connect(conn, server,
             user, password, database, 0, NULL, 0)) {
        fprintf(stderr, "%s\n", mysql_error(conn));
        exit(EXIT_FAILURE);
    }

    struct CmdArguments checkedArgs = ParseCmdArguments(conn, argv[1], argv[2], argv[3], argv[4]);

    batch_file = OpenFile(argv[4]);

    struct BatchTransaction transaction;
    int transactionCount = -1;
    while(fgets(line_buffer, sizeof(line_buffer), batch_file) != NULL)
    {
        transactionCount++;
        if(transactionCount > MAX_TRANSFERS)
        {
            printf("Too many transactions. Please use another file. Exit\n");
            break;
        }
        transaction.amount = -1.0f;

        if(!ProcessTransaction(conn, &transaction, line_buffer, checkedArgs.sender_id))
        {
            printf("Failed processing the transaction %d\n", transactionCount + 1);
            continue;
        }
     
        if(!PerformTransaction(conn, &transaction, checkedArgs.sender_id))
        {
            printf("Failed performing the transaction %d\n", transactionCount);
            continue;
        }
        
        printf("Transaction %d performed\n", transactionCount);
    }

    mysql_close(conn);

    fclose (batch_file);

    return 0;
}

void usage(char **argv)
{
    fprintf(stdout, "usage: %s <sender_id> <tan_id> <tan> <path_to_batch_file>", argv[0]);
    exit(EXIT_FAILURE);
}

struct CmdArguments ParseCmdArguments(MYSQL* conn, const char* sender, const char* tan_id, const char* tan, const char* fileName)
{
    struct CmdArguments args;
    if(!ConvertID(conn, sender, &args.sender_id, false))
    {
        printf("Sender id incorrect. Exit\n");
        exit(EXIT_FAILURE);
    }

    if(ConvertInt(tan_id, &args.tan_id, false)) {
        if (args.tan_id < 0 || args.tan_id > MAX_TANS) {
            printf("Incorrect tan id. Exit\n");
            exit(EXIT_FAILURE);
        }
    }
    else
    {
        printf("Unable to parse tan_id. Exit\n");
        exit(EXIT_FAILURE);
    }

    if (!CheckTAN(tan))
    {
        printf("Incorrect tan format. Exit\n");
        exit(EXIT_FAILURE);
    }
    else
    {
        strncpy(args.tan, tan, TAN_SIZE);
    }

    return args;
}

bool ConvertID(MYSQL *conn, const char* ID_Raw, int* ID_checked, bool batchArgument)
{
    int ID;
    if(!ConvertInt(ID_Raw, &ID, batchArgument))
    {
        printf("Unable to convert user ID\n");
        return false;
    }
    else if (!CheckID(conn, ID))
    {
        printf("User %d not found in database\n", ID);
        return false;
    }
    else
    {
        *ID_checked = ID;
        return true;
    }
}

bool ConvertInt(const char* rawInt, int* Int_checked, bool batchArgument)
{
    errno = 0;
    char* end = 0;
    long int result = strtol(rawInt, &end, 10);

    if (errno != 0)
    {
        printf("Conversion error %s\n", strerror(errno));
        return false;
    }
    else if (*end) {
        if(batchArgument && *end == batchSeperation.end)
        {}
        else
        {
            printf("%d Partially converted %ld, non converted %s\n", batchArgument,result, end);
            return false;
        }
    }

    *Int_checked = (int)result;
    return true;
}

bool CheckID(MYSQL *conn, const int ID)
{
    if (ID < 0 || ID > LONG_MAX)
    {
        printf("ID not in valid range\n");
        return false;
    }

    char sql_command[1024];
    sprintf(sql_command, "SELECT * FROM users WHERE id = '%d'", ID);
    if (!CheckDBOccurence(conn, sql_command))
    {
        return false;
    }

    return true;
}

bool CheckDBOccurence(MYSQL *conn, const char* query)
{
    MYSQL_RES *res;
    MYSQL_ROW row;
    if (mysql_query(conn, query)) {
        fprintf(stderr, "%s\n", mysql_error(conn));
        return false;
    }
    res = mysql_use_result(conn);

    if ((row = mysql_fetch_row(res)) == NULL)
    {
        printf("Entry does not exist in DB.\n");
        mysql_free_result(res);
        return false;
    }
    mysql_free_result(res);

    return true;
}

bool CheckTAN(const char* rawTan)
{
    int pos = 0;
    while (rawTan[pos] != '\0')
    {
        if (pos >= TAN_SIZE - 1)
        {
            printf("TAN too big\n");
            return false;
        }
        if (!CheckAllowedChar(rawTan[pos], false))
        {
            printf("Invalid char in TAN\n");
            return false;
        }
        pos++;
    }
    if (pos < TAN_SIZE - 1)
    {
        printf("TAN too short\n");
        return false;
    }

    return true;
}

bool CheckAllowedChar(const char c, const bool description)
{
    int i;
    for (i = 0; i < NUM_CHAR_RANGES; ++i)
    {
        if (c >= allowedCharRange[i].start && c <= allowedCharRange[i].end)
        {
            return true;
        }
    }
    return false;
}

FILE* OpenFile(const char* filePath)
{
    FILE* file = fopen(filePath, "r");
    if (file == NULL)
    {
        printf("Batch file could not be opened. Exit.");
        exit(EXIT_FAILURE);
    }
    else
    {
        return file;
    }
}

bool ProcessTransaction(MYSQL* conn, struct BatchTransaction* transaction, const char* line, int sender_id)
{
    struct BatchArguments argPositions[NUM_BATCH_ARGS];

    if(!ParseRawBatchFile(line, argPositions))
    {
        printf("Failed finding the batch file elements\n");
        return false;
    }

    if(!CheckBatchArguments(conn, argPositions, transaction, sender_id))
    {
        printf("Not all batch arguments were correct\n");
        return false;
    }

    return true;
}

bool CheckBatchArguments(MYSQL* conn, struct BatchArguments *argPositions, struct BatchTransaction* transaction, int sender_id)
{
    if(!ConvertID(conn, argPositions[0].start, &transaction->receiver_id, true))
    {
        printf("receiver id incorrect\n");
        return false;
    }
    if(transaction->receiver_id == sender_id)
    {
        printf("Sender and receiver are the same\n");
        return false;
    }

    if(ConvertFloat(argPositions[1].start, &transaction->amount, true)) {
        if (transaction->amount <= 0.0f) {
            printf("Amount incorrect\n");
            return false;
        }
    }
    else
    {
        printf("Failed to convert amount to float\n");
        return false;
    }

    if(!CheckSenderBalance(conn, sender_id, transaction->amount))
    {
        return false;
    }
    
    if (!CheckDescription(argPositions[2]))
    {
        printf("Error in description\n");
        return false;
    }
    else
    {
        int length = argPositions[2].end - argPositions[2].start;
        strncpy(transaction->description, argPositions[2].start, length);
        transaction->description[length] = '\0';
    }

    return true;
}

bool ParseRawBatchFile(const char* lineBuffer, struct BatchArguments *args)
{
    char* currPos = lineBuffer;
    int i;
    for (i = 0; i < NUM_BATCH_ARGS; ++i)
    {
        if (!FindBatchArgument(&args[i], currPos, lineBuffer, maxBatchArgSize[i]))
        {
            args[i].start = NULL;
            args[i].end = NULL;
            printf("Error finding transaction argument %d\n", i + 1);
            return false;
        }
        currPos = args[i].end + 1;
    }
    return true;
}

bool FindBatchArgument(struct BatchArguments *args, const char* argStart, const char* lineStart, int maxSize)
{
    char* currPos = argStart;
    args->start = NULL;
    args->end = NULL;

    while (currPos - lineStart < BUFFER_SIZE)
    {
        if(*currPos == '\n' || *currPos == '\0')
        {
            printf("Reached end of line\n");
            return false;
        }
        if(currPos - lineStart == BUFFER_SIZE -1)
        {
            printf("Reached maximum of line\n");
            return false;
        }
        if (*currPos == batchSeperation.start)
        {
            args->start = currPos + 1;
        }

        if (*currPos == batchSeperation.end)
        {
            if (args->start == NULL)
            {
                printf("No argument start found\n");
                return false;
            }
            args->end = currPos;
            break;
        }
        currPos++;
    }

    if (args->start == NULL || args->end == NULL)
    {
        printf("Failed to find argument\n");
        return false;
    }
    int size = args->end - args->start;
    if (size > maxSize || size <= 0)
    {
        printf("Argument has wrong size %d\n", size);
        return false;
    }
    
    return true;
}

bool ConvertFloat(const char* rawFloat, float* float_Checked, bool batchArgument)
{
    errno = 0;
    char* end = 0;
    double result = strtod(rawFloat, &end);

    if (errno != 0)
    {
        printf("Conversion error %s\n", strerror(errno));
        return false;
    }
    else if (*end)
    {
        if(batchArgument && *end == batchSeperation.end)
        {}
        else
        {
            printf("Partially converted %f, non converted %s\n", result, end);
            return false;
        }
    }
    *float_Checked = (float)result;
    return true;
}

bool CheckSenderBalance(MYSQL* conn, int sender_id, float amount)
{
    char sql_command[1024];
    sprintf(sql_command, "SELECT * FROM accounts WHERE user_id = '%d' AND balance > '%f'", sender_id, amount);
    if (!CheckDBOccurence(conn, sql_command))
    {
        printf("Sender balance not sufficient.\n");
        return false;
    }

    return true;
}

bool CheckDescription(struct BatchArguments raw)
{
    char* currPos;
    for (currPos = raw.start; currPos < raw.end; ++currPos)
    {
        if(*currPos == '\0')
        {
            break;
        }
        if (!CheckAllowedChar(*currPos, true))
        {
            printf("Not allowed description character transform to whitespace\n");
            *currPos = ' ';
        }
    }

    return true;
}

bool PerformTransaction(MYSQL* conn, struct BatchTransaction* transaction, int sender_id)
{
    char sql_command[1024];
    int approved = 0;
    if (transaction->amount <= 10000) {
        approved = 1;
    }
    sprintf(sql_command, "INSERT INTO transactions (sender_id, receiver_id, amount, approved, description) VALUES ('%d', '%d', '%f', '%d', '%s')", sender_id, transaction->receiver_id, transaction->amount, approved, transaction->description);
    if (mysql_query(conn, sql_command)) {
        printf("Failed adding transaction to db\n");
        fprintf(stderr, "%s\n", mysql_error(conn));
        return false;
    }

    if(transaction->amount <= 10000)
    {
        sprintf(sql_command, "UPDATE accounts SET balance = balance - %f WHERE user_id = '%d'", transaction->amount, sender_id);
        if (mysql_query(conn, sql_command)) {
            printf("Failed updating sender balance\n");
            fprintf(stderr, "%s\n", mysql_error(conn));
            return false;
        }
        sprintf(sql_command, "UPDATE accounts SET balance = balance + %f WHERE user_id = '%d'", transaction->amount, transaction->receiver_id);
        if (mysql_query(conn, sql_command)) {
            printf("Failed updating receiver balance\n");
            fprintf(stderr, "%s\n", mysql_error(conn));
            return false;
        }
    }

    return true;
}

