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
#define TAN_SIZE 16
#define MAX_TANS 99
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

struct Description
{
    char* startPos;
    int size;
};

struct Pair
{
    char start;
    char end;
};
struct Pair batchSeperation = (struct Pair) { '<', '>' };

void usage(char **argv);
struct CmdArguments ParseCmdArguments(MYSQL* conn, const char* sender, const char* tan_id, const char* tan, const char* fileName);
bool ConvertID(MYSQL *conn, const char* ID_Raw, int* ID_checked);
bool CheckID(MYSQL *conn, const int ID);
bool ConvertInt(const char* rawInt, int* Int_checked, bool batchArgument);
bool CheckDBOccurence(MYSQL *conn, const char* query);

//returns the string position and size of the description
struct Description ParseDescription(char* amountPos)
{
    struct Description result;
    char* currPos;
    for(currPos = amountPos; currPos - amountPos < DESC_BUFFER_SIZE && *currPos != ' '; currPos += 1);
    result.startPos = currPos + 1;
    for(currPos = result.startPos; *currPos != '\0'; currPos += 1);
    result.size = currPos - result.startPos - 1;
    if(result.size > DESC_BUFFER_SIZE)
    {
        exit(1);
    }
    return result;
}

int main(int argc, char **argv) {
    //Format: <receiver_id> <amount>
    int tan_id, receiver_id, first_space_location = 0;
    double amount;
    char tan[TAN_SIZE];
    FILE *batch_file;
    char line_buffer[BUFFER_SIZE];
    char *amount_position;
    char *tan_position;

    if(argc != 5)
    {
        usage(argv);
    }

    batch_file = fopen (argv[4],"r");
    if (batch_file == NULL)
    {
        printf("Batch file could not be opened. Exit.");
        exit(EXIT_FAILURE);
    }

    memcpy(tan, argv[3], 15);
    tan[15] = '\0';
    tan_id = atoi(argv[2]);

    MYSQL *conn;
    MYSQL_RES *res;
    MYSQL_ROW row;
    char sql_command[1024];
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

    while(fgets(line_buffer, sizeof(line_buffer), batch_file) != NULL)
    {
        receiver_id = atoi(line_buffer);

        for(first_space_location = 0; first_space_location < BUFFER_SIZE && line_buffer[first_space_location] != ' '; first_space_location += 1);
        //+1 to jump over space
        amount_position = line_buffer + first_space_location + 1;
        amount = atof(amount_position);

        if (amount <= 0)
        {
            fprintf(stderr, "Negative or an amount of zero is not allowed!\n");
            exit(EXIT_FAILURE);
        }

        //printf("Current line: %d - %f\n", receiver_id, amount);

        //1. recipient exists? 2. confirmation required? 3. add transaction 4. if no confirmation: change balances

         sprintf(sql_command, "SELECT * FROM users WHERE id = '%d'", receiver_id);
         if (mysql_query(conn, sql_command)) {
             fprintf(stderr, "%s\n", mysql_error(conn));
             exit(1);
         }
         res = mysql_use_result(conn);


         if ((row = mysql_fetch_row(res)) == NULL)
         {
             printf("Recipient does not exist! Exit.");
             mysql_close(conn);
             exit(EXIT_FAILURE);
         }
         mysql_free_result(res);


         sprintf(sql_command, "SELECT * FROM accounts WHERE user_id = '%d' AND balance > '%f'", checkedArgs.sender_id, amount);
         if (mysql_query(conn, sql_command)) {
             fprintf(stderr, "%s\n", mysql_error(conn));
             exit(1);
         }
         res = mysql_use_result(conn);


         if ((row = mysql_fetch_row(res)) == NULL)
         {
             printf("Balance not sufficient! Exit.");
             mysql_close(conn);
             exit(EXIT_FAILURE);
         }
         mysql_free_result(res);

         //get the description
        struct Description desc = ParseDescription(amount_position);
        if (amount <= 10000)
        {
            sprintf(sql_command, "INSERT INTO transactions (sender_id, receiver_id, amount, approved, description) VALUES ('%d', '%d', '%f', '1', '%.*s')", checkedArgs.sender_id, receiver_id, amount, desc.size, desc.startPos);
        }
        else
        {
            sprintf(sql_command, "INSERT INTO transactions (sender_id, receiver_id, amount, approved, description) VALUES ('%d', '%d', '%f', '0', '%.*s')", checkedArgs.sender_id, receiver_id, amount, desc.size, desc.startPos);
        }
        if (mysql_query(conn, sql_command)) {
            fprintf(stderr, "%s\n", mysql_error(conn));
            exit(1);
        }


        if(amount <= 10000)
        {
            sprintf(sql_command, "UPDATE accounts SET balance = balance - %f WHERE user_id = '%d'", amount, checkedArgs.sender_id);
            if (mysql_query(conn, sql_command)) {
                fprintf(stderr, "%s\n", mysql_error(conn));
                exit(1);
            }


            sprintf(sql_command, "UPDATE accounts SET balance = balance + %f WHERE user_id = '%d'", amount, receiver_id);
            if (mysql_query(conn, sql_command)) {
                fprintf(stderr, "%s\n", mysql_error(conn));
                exit(1);
            }

        }

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
    if(!ConvertID(conn, sender, &args.sender_id))
    {
        printf("Sender id incorrect. Exit\n");
        exit(EXIT_FAILURE);
    }

    if(ConvertInt(tan_id, &args.tan_id, false)) {
        if (args.tan_id < 0 || args.tan_id > MAX_TANS) {
            printf("Incorrect tan id. Exit");
            exit(EXIT_FAILURE);
        }
    }
    else
    {
        printf("Unable to parse tan_id. Exit");
        exit(EXIT_FAILURE);
    }

    return args;
}

bool ConvertID(MYSQL *conn, const char* ID_Raw, int* ID_checked)
{
    int ID;
    if(!ConvertInt(ID_Raw, &ID, false))
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
    else if (*end || batchArgument && *end != batchSeperation.end) {
        printf("Partially converted %ld, non converted %s\n", result, end);
        return false;
    }
    else
    {
        *Int_checked = (int)result;
        return true;
    }
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

