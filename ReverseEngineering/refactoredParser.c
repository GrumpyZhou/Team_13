#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <mysql/mysql.h>

static int account_id_size = 10;
static int account_name_size = 30;
static int amount_size = 8;
static int remarks_size = 128;

static MYSQL* db_connection = NULL;
static int opt_port_num = 3306;

static MYSQL_BIND parameters_TransCode[3];
static int int_data_TransCode[2];
static unsigned long int_length_TransCode[2];
static char str_data_TransCode[256];
static unsigned long str_length_TransCode;

static MYSQL_BIND parameters_Transaction[10];
static MYSQL_TIME currTime;
static unsigned long parameters_length[10];
static unsigned long result_length;
static int int_data_Transaction[5];
static char str_data_Transaction[3][256];
static float float_data_Transaction[1];

struct transaction_row
{
	char* account_id;
	char* account_name;
	char* amount;
	char* remarks;
};

struct transaction_row construct_transaction_row()
{
	struct transaction_row row;
	row.account_id = malloc(sizeof(char) * (account_id_size + 1));
	row.account_name = malloc(sizeof(char) * (account_name_size + 1));
	row.amount = malloc(sizeof(char) * (amount_size + 1));
	row.remarks = malloc(sizeof(char) * (remarks_size + 1));
	return row;
}

void desctruct_transaction_row(struct transaction_row row)
{
	free(row.account_id);
	free(row.account_name);
	free(row.amount);
	free(row.remarks);
}

MYSQL* initalizeDB()
{
	MYSQL * db = mysql_init(NULL);
	if(db != NULL)
	{
		return db;
	}
	else
	{
		puts("Error in connecting to the database.");
		exit(1);
	}
}

void connectToDB(MYSQL* mysql, const char* host, const char* user, 
const char* passwd, const char* db, unsigned int port)
{
	const char *unix_socket = NULL;
	unsigned long client_flag = 0;
	if(!mysql_real_connect(mysql, host, user, passwd, db, port, unix_socket, client_flag))
	{
		puts("Error in connecting to the database.");
		mysql_close(mysql);
		exit(1);
	}
}

MYSQL_STMT* initializeStatement(MYSQL* mysql)
{
	MYSQL_STMT* handle = mysql_stmt_init(mysql);
	if(handle == NULL)
	{
		int size = 1;
		int count = 43;
		fwrite("Error in initializing the query statement.\n", size, count, stderr);
		return 0;
	}
	return handle;
}

int prepareStatement(MYSQL_STMT* stmt, const char* stmt_str, int length)
{
	if(mysql_stmt_prepare(stmt, stmt_str, length) != 0)
	{
		int size = 1;
		int count = 40;
		fwrite("Error in preparing the query statement.\n", size, count, stderr);
		const char* error = mysql_stmt_error(stmt);
		fprintf(stderr, " %s\n", error);
		return 0;
	}
	return 1;
}

int bindParameters(MYSQL_STMT *stmt, MYSQL_BIND *bind)
{
	if(mysql_stmt_bind_param(stmt, bind) != 0)
	{
		int size = 1;
		int count = 35;
		fwrite("Error in binding query parameters.\n", size, count, stderr);
		const char* error = mysql_stmt_error(stmt);
		fprintf(stderr, " %s\n", error);
		return 0;
	}
	return 1;
}

int bindResult(MYSQL_STMT *stmt, MYSQL_BIND *bind)
{
	if(mysql_stmt_bind_result(stmt, bind) != 0)
	{
		int size = 1;
		int count = 31;
		fwrite("Error in binding query result.\n", size, count, stderr);
		const char* error = mysql_stmt_error(stmt);
		fprintf(stderr, " %s\n", error);
		return 0;
	}
	return 1;
}

int executeStatement(MYSQL_STMT *stmt)
{
	if(mysql_stmt_execute(stmt) != 0)
	{
		int size = 1;
		int count = 26;
		fwrite("Error in executing query.\n", size, count, stderr);
		const char* error = mysql_stmt_error(stmt);
		fprintf(stderr, " %s\n", error);
		return 0;
	}
	return 1;
}

int storeResult(MYSQL_STMT *stmt)
{
	if(mysql_stmt_store_result(stmt) != 0)
	{
		int size = 1;
		int count = 35;
		fwrite("Error in storing statement result.\n", size, count, stderr);
		const char* error = mysql_stmt_error(stmt);
		fprintf(stderr, " %s\n", error);
		return 0;
	}
	return 1;
}

int freeResult(MYSQL_STMT* stmt)
{
	if(mysql_stmt_free_result(stmt) != 0)
	{
		int size = 1;
		int count = 35;
		fwrite("Error in freeing the query result.\n", size, count, stderr);
		const char* error = mysql_stmt_error(stmt);
		fprintf(stderr, " %s\n", error);
		return 0;
	}
	return 1;
}

int	closeStatement(MYSQL_STMT* stmt)
{
	if(mysql_stmt_close(stmt) != 0)
	{
		int size = 1;
		int count = 38;
		fwrite("Error in closing the query statement.\n", size, count, stderr);
		const char* error = mysql_stmt_error(stmt);
		fprintf(stderr, " %s\n", error);
		return 0;
	}
	return 1;
}

int isAccount(MYSQL* mysql, int receiverID)
{
	const char* request = "SELECT `ID` FROM `TBL_ACCOUNT` WHERE `ACCOUNT_ID` = ?";
	MYSQL_STMT* statement = initializeStatement(mysql);
	MYSQL_BIND bind[1], result[1];
	int bindBuffer, resultBuffer;
	unsigned long int bindLength, resultLength;
	int rowNonZero = 0;
	
	int length = 0;
	while((*request++) != 0)
	{
		length++;
	}
	length -= 1;
	prepareStatement(statement, request, length);
	
	bind[0].buffer_type= MYSQL_TYPE_LONG;
	bind[0].buffer= (char *)&bindBuffer;
	bind[0].buffer_length= 2;
	bind[0].is_null= 0;
	bind[0].length= &bindLength;
	
	result[0].buffer_type= MYSQL_TYPE_LONG;
	result[0].buffer= (char *)&resultBuffer;
	result[0].buffer_length= 2;
	result[0].is_null= 0;
	result[0].length= &resultLength;
	
	bindParameters(statement, bind);
	bindResult(statement, result);
	
	bindBuffer = receiverID;
	
	executeStatement(statement);
	storeResult(statement);
	
	if(mysql_stmt_num_rows(statement) != 0)
	{
		rowNonZero = 1;
	}
	else
	{
		rowNonZero = 0;
	}
	
	freeResult(statement);
	closeStatement(statement);
	
	return rowNonZero;
}

 int isBalanceSufficient(MYSQL* mysql, int senderID, float amount)
{
	const char* query = "SELECT `ID` FROM `TBL_ACCOUNT` WHERE `ACCOUNT_ID` = ? AND `BALANCE >= ?";
	
	MYSQL_STMT* statement = initializeStatement(mysql);
	MYSQL_BIND bind[2], result[1];
	int bind1Buffer;
	float bind2Buffer;
	int resultBuffer;
	unsigned long bindLength[2];
	unsigned long resultLength;
	int rowNonZero = 0;
	
	int length = 0;
	while((*query++) != 0)
	{
		length++;
	}
	length -= 1;
	prepareStatement(statement, query, length);
	
	bind[0].buffer_type= MYSQL_TYPE_LONG;
	bind[0].buffer= (char *)&bind1Buffer;
	bind[0].buffer_length= 2;
	bind[0].is_null= 0;
	bind[0].length= &bindLength[0];
	
	bind[1].buffer_type= MYSQL_TYPE_FLOAT;
	bind[1].buffer= (char *)&bind2Buffer;
	bind[1].buffer_length= 2;
	bind[1].is_null= 0;
	bind[1].length= &bindLength[1];
	
	result[0].buffer_type= MYSQL_TYPE_LONG;
	result[0].buffer= (char *)&resultBuffer;
	result[0].buffer_length= 2;
	result[0].is_null= 0;
	result[0].length= &resultLength;
	
	bindParameters(statement, bind);
	bindResult(statement, result);
	
	bind1Buffer = senderID;
	bind2Buffer = amount;
	
	executeStatement(statement);
	storeResult(statement);
	
	if(mysql_stmt_num_rows(statement) != 0)
	{
		rowNonZero = 1;
	}
	else
	{
		rowNonZero = 0;
	}
	
	freeResult(statement);
	closeStatement(statement);
	
	return rowNonZero;
}

int addTransactionCode(MYSQL* mysql, int costumerID, const char* sourceString)
{
	const char* query = "INSERT INTO `TBL_TRANSACTION_CODE`(`CUSTOMER_ID`, `CODE`, `IS_USED`) VALUES(?, ?, ?)";
	int result;
	
	MYSQL_STMT* statement = initializeStatement(mysql);
	if(statement == 0)
	{
		puts("Error in adding transaction code");
		return 0;
	}
	else
	{
		int length = 0;
		while((*query++) != 0)
		{
			length++;
		}
		length -= 1;
		result = prepareStatement(statement, query, length);
		if(result == 0)
		{
			puts("Error in adding transaction code");
			return result;
		}
		else
		{
			parameters_TransCode[0].buffer_type= MYSQL_TYPE_LONG;
			parameters_TransCode[0].buffer= (char *)&int_data_TransCode[0];
			parameters_TransCode[0].buffer_length= 2;
			parameters_TransCode[0].is_null= 0;
			parameters_TransCode[0].length= &int_length_TransCode[0];
			
			parameters_TransCode[1].buffer_type= MYSQL_TYPE_STRING;
			parameters_TransCode[1].buffer= (char *)str_data_TransCode;
			parameters_TransCode[1].buffer_length= 256;
			parameters_TransCode[1].is_null= 0;
			parameters_TransCode[1].length= &str_length_TransCode;
			
			parameters_TransCode[2].buffer_type= MYSQL_TYPE_LONG;
			parameters_TransCode[2].buffer= (char *)&int_data_TransCode[1];
			parameters_TransCode[2].buffer_length= 2;
			parameters_TransCode[2].is_null= 0;
			parameters_TransCode[2].length= &int_length_TransCode[1];
			
			result = bindParameters(statement, parameters_TransCode);
			if(result == 0)
			{
				puts("Error in adding transaction code");
				return result;
			}
			else
			{
				int_data_TransCode[0] = costumerID;
				strncpy(str_data_TransCode, sourceString, 256);
				
				int length = 0;
				char* strPtr = str_data_TransCode;
				while((*strPtr++) != 0)
				{
					length++;
				}
				length -= 1;
				str_length_TransCode = length;
				int_data_TransCode[1] = 1;
				
				result = executeStatement(statement);
				if(result == 0)
				{
					puts("Error in adding transaction code");
					return result;
				}
				else
				{
					result = freeResult(statement);
					if(result == 0)
					{
						puts("Error in adding transaction code");
						return result;
					}
					else
					{
						result = closeStatement(statement);
						if(result != 0)
						{
							return result;
						}
						else
						{
							puts("Error in adding transaction code");
							return result;
						}
					}
				}
			}
		}
	}			
}

int setIsUsedTransactionCode(MYSQL* mysql, int costumerID, const char* code)
{
	const char* query = "UPDATE `TBL_TRANSACTION_CODE` SET IS_USED = ? WHERE `CUSTOMER_ID` = ? AND `CODE` = ?";
	int result;
	
	MYSQL_STMT* statement = initializeStatement(mysql);
	if(statement == 0)
	{
		puts("Error in updating transaction code");
		return 0;
	}
	else
	{
		int length = 0;
		while((*query++) != 0)
		{
			length++;
		}
		length -= 1;
		result = prepareStatement(statement, query, length);
		if(result == 0)
		{
			puts("Error in updating transaction code");
			return result;
		}
		else
		{
			parameters_TransCode[1].buffer_type= MYSQL_TYPE_LONG;
			parameters_TransCode[1].buffer= (char *)&int_data_TransCode[0];
			parameters_TransCode[1].buffer_length= 2;
			parameters_TransCode[1].is_null= 0;
			parameters_TransCode[1].length= &int_length_TransCode[0];
			
			parameters_TransCode[2].buffer_type= MYSQL_TYPE_LONG;
			parameters_TransCode[2].buffer= (char *)&int_data_TransCode[1];
			parameters_TransCode[2].buffer_length= 2;
			parameters_TransCode[2].is_null= 0;
			parameters_TransCode[2].length= &int_length_TransCode[1];
			
			parameters_TransCode[3].buffer_type= MYSQL_TYPE_STRING;
			parameters_TransCode[3].buffer= (char *)str_data_TransCode;
			parameters_TransCode[3].buffer_length= 256;
			parameters_TransCode[3].is_null= 0;
			parameters_TransCode[3].length= &str_length_TransCode;
			
			result = bindParameters(statement, parameters_TransCode);
			if(result == 0)
			{
				puts("Error in updating transaction code");
				return result;
			}
			else
			{
				int_data_TransCode[0] = 1;
				strncpy(str_data_TransCode, code, 256);
				
				int length = 0;
				char* dataPtr = str_data_TransCode;
				while((*dataPtr++) != 0)
				{
					length++;
				}
				length -= 1;
				str_length_TransCode = length;
				int_data_TransCode[1] = costumerID;
				
				result = executeStatement(statement);
				if(result == 0)
				{
					puts("Error in updating transaction code");
					return result;
				}
				else
				{
					result = freeResult(statement);
					if(result == 0)
					{
						puts("Error in updating transaction code");
						return result;
					}
					else
					{
						result = closeStatement(statement);
						if(result != 0)
						{
							if(mysql_stmt_affected_rows(statement) != 0)
							{
								return addTransactionCode(mysql, costumerID, code);
							}
						}
						else
						{
							puts("Error in updating transaction code");
							return result;
						}
					}
				}
			}
		}
	}
	return result;
}

void getCurrentDateTime(int* year, int* month, int* day, int* hours, int* minutes, int* seconds)
{
	time_t timer;
	struct tm* timeInfo;
	
	time(&timer);
	timeInfo = localtime(&timer);
	*year =  timeInfo->tm_year + 1900;
	*month = timeInfo->tm_mon + 1;
	*day = timeInfo->tm_mday;
	*hours = timeInfo->tm_hour;
	*minutes = timeInfo->tm_min;
	*seconds = timeInfo->tm_sec;
	return;
}

int updateAccountBalance(MYSQL* mysql, int senderID, float amount, const char* action)
{
	char* query;
	int result;
	
	if(strcmp(action, "increment") == 0)
	{
		query = "UPDATE `TBL_ACCOUNT` SET BALANCE = BALANCE + ? WHERE `ACCOUNT_ID` = ?";
	}
	else if(strcmp(action, "decrement") == 0)
	{
		query = "UPDATE `TBL_ACCOUNT` SET BALANCE = BALANCE - ? WHERE `ACCOUNT_ID` = ?";
	}
		
	MYSQL_STMT* statement = initializeStatement(mysql);
	if(statement == 0)
	{
		puts("Error in updating account balance");
		return 0;
	}
	else
	{
		int length = 0;
		while((*query++) != 0)
		{
			length++;
		}
		length -= 1;
		
		result = prepareStatement(statement, query, length);
		if(result == 0) 
		{
			puts("Error in updating account balance");
			return 0;
		}
		else
		{
			MYSQL_BIND parameters[2];
			int int_data;
			float float_data;
			unsigned long bind_length;
			
			parameters[0].buffer_type= MYSQL_TYPE_FLOAT;
			parameters[0].buffer= (char *)&float_data;
			parameters[0].buffer_length= 2;
			parameters[0].is_null= 0;
			parameters[0].length= &bind_length;
			
			parameters[2].buffer_type= MYSQL_TYPE_LONG;
			parameters[2].buffer= (char *)&int_data;
			parameters[2].buffer_length= 2;
			parameters[2].is_null= 0;
			parameters[2].length= &bind_length;
			
			result = bindParameters(statement, parameters);
			if(result == 0)
			{
				puts("Error in updating account balance");
				return 0;
			}
			else
			{
				float_data = amount;
				int_data = senderID;
				result = executeStatement(statement);
				if(result == 0)
				{
					puts("Error in updating account balance");
					return 0;
				}
				else
				{
					result = freeResult(statement);
					if(result == 0)
					{
						puts("Error in updating account balance");
						return 0;
					}
					else
					{
						result = closeStatement(statement);
						if(result == 0)
						{
							puts("Error in updating account balance.");
						}
						return result;
					}
				}
			}
		}
	}
}

int addTransaction(MYSQL* mysql, int customerID, const char* customerName,
	int senderID, int receiverID, const char* receiverName, float amount, 
	const char* description)
{
	const char* query = "INSERT INTO `TBL_TRANSACTION`(`TRANSACTION_DATE`, `FROM_ACCOUNT_I"
						"D`, `FROM_ACCOUNT_NAME`, `TO_ACCOUNT_ID`, `TO_ACCOUNT_NAME`,`AMOU"
						"NT`, `REMARKS`, `IS_ON_HOLD`, `IS_REJECTED`, `IS_CLOSED`) VALUES("
						"?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	int result;
	
	int onHold = 0;
	int reject = 0;
	int closed = 1;
	if(amount > 10000)
	{
		onHold = 1;
		closed = 0;
	}
	
	MYSQL_STMT* statement = initializeStatement(mysql);
	if(statement == 0)
	{
		puts("Error in adding transaction");
		return 0;
	}
	else
	{
		int length = 0;
		while((*query++) != 0)
		{
			length++;
		}
		length -= 1;
		result = prepareStatement(statement, query, length);
		if(result == 0)
		{
			puts("Error in updating transaction code");
			return result;
		}
		else
		{
			//TRANSACTION_DATE
			parameters_Transaction[0].buffer_type= MYSQL_TYPE_DATETIME;
			parameters_Transaction[0].buffer= (char *)&currTime;
			parameters_Transaction[0].buffer_length= 2;
			parameters_Transaction[0].is_null= 0;
			parameters_Transaction[0].length= &parameters_length[0];
			
			//FROM_ACCOUNT_ID
			parameters_Transaction[1].buffer_type= MYSQL_TYPE_LONG;
			parameters_Transaction[1].buffer= (char *)&int_data_Transaction[0];
			parameters_Transaction[1].buffer_length= 2;
			parameters_Transaction[1].is_null= 0;
			parameters_Transaction[1].length= &parameters_length[1];
			
			//FROM_ACCOUNT_NAME
			parameters_Transaction[2].buffer_type= MYSQL_TYPE_STRING;
			parameters_Transaction[2].buffer= (char *)str_data_Transaction[0];
			parameters_Transaction[2].buffer_length= 256;
			parameters_Transaction[2].is_null= 0;
			parameters_Transaction[2].length= &parameters_length[2];
			
			//TO_ACCOUNT_ID
			parameters_Transaction[3].buffer_type= MYSQL_TYPE_LONG;
			parameters_Transaction[3].buffer= (char *)int_data_Transaction[1];
			parameters_Transaction[3].buffer_length= 2;
			parameters_Transaction[3].is_null= 0;
			parameters_Transaction[3].length= &parameters_length[3];
			
			//TO_ACCOUNT_NAME
			parameters_Transaction[4].buffer_type= MYSQL_TYPE_STRING;
			parameters_Transaction[4].buffer= (char *)str_data_Transaction[1];
			parameters_Transaction[4].buffer_length= 256;
			parameters_Transaction[4].is_null= 0;
			parameters_Transaction[4].length= &parameters_length[4];
			
			//AMOUNT
			parameters_Transaction[5].buffer_type= MYSQL_TYPE_FLOAT;
			parameters_Transaction[5].buffer= (char *)&float_data_Transaction;
			parameters_Transaction[5].buffer_length= 2;
			parameters_Transaction[5].is_null= 0;
			parameters_Transaction[5].length= &parameters_length[5];
			
			//REMARKS
			parameters_Transaction[6].buffer_type= MYSQL_TYPE_STRING;
			parameters_Transaction[6].buffer= (char *)str_data_Transaction[2];
			parameters_Transaction[6].buffer_length= 256;
			parameters_Transaction[6].is_null= 0;
			parameters_Transaction[6].length= &parameters_length[6];
			
			//IS_ON_HOLD
			parameters_Transaction[7].buffer_type= MYSQL_TYPE_TINY;
			parameters_Transaction[7].buffer= (char *)&int_data_Transaction[2];
			parameters_Transaction[7].buffer_length= 2;
			parameters_Transaction[7].is_null= 0;
			parameters_Transaction[7].length= &parameters_length[7];
			
			//IS_REJECTED
			parameters_Transaction[8].buffer_type= MYSQL_TYPE_TINY;
			parameters_Transaction[8].buffer= (char *)&int_data_Transaction[3];
			parameters_Transaction[8].buffer_length= 2;
			parameters_Transaction[8].is_null= 0;
			parameters_Transaction[8].length= &parameters_length[8];
			
			//IS_CLOSED
			parameters_Transaction[9].buffer_type= MYSQL_TYPE_TINY;
			parameters_Transaction[9].buffer= (char *)&int_data_Transaction[4];
			parameters_Transaction[9].buffer_length= 2;
			parameters_Transaction[9].is_null= 0;
			parameters_Transaction[9].length= &parameters_length[9];
			
			result = bindParameters(statement, parameters_Transaction);
			if(result == 0)
			{
				puts("Error in adding transaction");
				return result;
			}
			else
			{
				int_data_Transaction[0] = senderID;
				int_data_Transaction[1] = receiverID;
				int_data_Transaction[2] = onHold;
				int_data_Transaction[3] = reject;
				int_data_Transaction[4] = closed;
				
				strcpy(str_data_Transaction[0], customerName);
				int length = 0;
				char* strPtr = str_data_Transaction[0];
				while((*strPtr++) != 0)
				{
					length++;
				}
				length -= 1;
				parameters_length[2] = length;
				
				strcpy(str_data_Transaction[1], receiverName); 
				length = 0;
				strPtr = str_data_Transaction[1];
				while((*strPtr++) != 0)
				{
					length++;
				}
				length -= 1;
				parameters_length[3] = length;
				
				strcpy(str_data_Transaction[2], description);
				length = 0;
				strPtr = str_data_Transaction[2];
				while((*strPtr++) != 0)
				{
					length++;
				}
				length -= 1;
				parameters_length[6] = length;
				
				float_data_Transaction[0] = amount;
				
				getCurrentDateTime(&currTime.year, &currTime.month, &currTime.day,
					&currTime.hour, &currTime.minute, &currTime.second);
				
				result = executeStatement(statement);
				if(result == 0)
				{
					puts("Error in adding transaction");
					return result;
				}
				else
				{
					result = freeResult(statement);
					if(result == 0)
					{
						puts("Error in adding transaction");
						return result;
					}
					else
					{
						result = closeStatement(statement);
						if(result == 0)
						{
							puts("Error in adding transaction");
							return result;
						}
						else
						{
							if(onHold == 0)
							{
								return 1;
							}
							else
							{
								result = updateAccountBalance(mysql, senderID, amount, "decrement");
								if(result == 0)
								{
									printf("Error in adding transaction");
									return result;
								}
								else
								{
									//BUG not checked if succesfull
									updateAccountBalance(mysql, receiverID, amount, "increment");
									return 1;
								}
							}
						}
					}
				}
			}
		}
	}
}

int makeTransfer(MYSQL* mysql, int customerID, const char* senderName, 
	const char* code, int sender_ID, int receiver_ID, const char* receiverName, 
	float amount, const char* description)
{
	if(amount <= 0)
	{
		puts("Incorrect amount for the transfer");
		return 0;
	}
	
	if(customerID == receiver_ID)
	{
		puts("Recipient Account same as own account");
		return 0;
	}
	
	if(isAccount(mysql, receiver_ID) == 0)
	{
		puts("Recipient account does not exist");
		return 0;
	}
	
	if(isBalanceSufficient(mysql, sender_ID, amount) == 0)
	{
		puts("Insuffiecient funds for the transfer");
		return 0;
	}
	
	mysql_autocommit(mysql, 0);
	if(setIsUsedTransactionCode(mysql, customerID, code) == 0)
	{
		puts("Error in updating TAN(transaction code).");
		mysql_rollback(mysql);
		return 0;
	}
	
	if(addTransaction(mysql, customerID, senderName, sender_ID, receiver_ID, 
			receiverName, amount, description) == 0)
	{
		puts("Error in adding transaction");
		mysql_rollback(mysql);
		return 0;
	}
	
	mysql_commit(mysql);
	puts("Transaction was processed successfully");
	return 1;
}

void closeDB(MYSQL* mysql)
{
	mysql_close(mysql);
}

int processTransfer(int customerID, const char* customerName, const char* code,
	int senderID, int receiverID, const char* receiverName, float amount, 
	const char* description, const char* host, const char* user,
	const char* passwd, const char* db)
{
	db_connection = initalizeDB();
	connectToDB(db_connection, host, user, passwd, db, opt_port_num);
	int result = makeTransfer(db_connection, customerID, customerName, code, 
		senderID, receiverID, receiverName,	amount, description);
	closeDB(db_connection);
	return result;
}

int main( int argc, const char* argv[] )
{
	const char* fileName = argv[1];
	const char* customerID = argv[2];
	const char* customerName = argv[3];
	const char* customerAccountId = argv[4];
	const char* transactionCode = argv[5];
	const char* mysqlHost = argv[6];
	const char* mysqlUser = argv[7];
	const char* mysqlPassword = argv[8];
	const char* mysqlDatabase = argv[9];
	
	FILE* pFile;
	pFile = fopen(fileName, "r");
	int result = 0;
	
	if(pFile != NULL)
	{
		struct transaction_row row = construct_transaction_row();
		int a = 0;
		int numRows = 0;
		int b = 0;
		char currentChar = 0;
		char prevChar = 0;
		do
		{
			currentChar = fgetc(pFile);
			if(currentChar == '\n')
			{
				if(processTransfer(atoi(customerID), customerName, transactionCode,
					atoi(customerAccountId), atoi(row.account_id), row.account_name, 
					strtof(row.amount, 0), row.remarks, mysqlHost, mysqlUser, 
					mysqlPassword, mysqlDatabase))
				{
					result = 1;
				}
				else
				{
					result = 0;
				}	
				a = 0;
				numRows++;
			}
			if(currentChar != ';' || prevChar == '\\')
			{
				if(currentChar != '\\')
				{
					if(a == 0)
					{
						if(b < account_id_size)
						{
							row.account_id[b] = currentChar;
							b++;
						}
						else if(b == account_id_size)
						{
							row.account_id[b] = 0;
							b++;
						}
					}
					else if(a == 1)
					{
						if(b < account_name_size)
						{
							row.account_name[b] = currentChar;
							b++;
						}
						else if(b == account_name_size)
						{
							row.account_name[b] = 0;
							b++;
						}
					}
					else if(a == 2)
					{
						if(b < amount_size)
						{
							row.amount[b] = currentChar;
							b++;
						}
						else if(b == amount_size)
						{
							row.amount[b] = 0;
							b++;
						}
					}
					else if(a == 3)
					{
						if(b < remarks_size)
						{
							row.remarks[b] = currentChar;
							b++;
						}
						else if(b == remarks_size)
						{
							row.remarks[b] = 0;
							b++;
						}
					}
					
					if(currentChar == '\n')
					{
						b = 0;
					}
				}
			}
			else
			{
				if(a == 0)
				{
					if(b < account_id_size)
					{
						row.account_id[b] = 0;
					}
				}
				else if(a == 1)
				{
					if(b < account_name_size)
					{
						row.account_name[b] = 0;
					}
				}
				else if(a == 2)
				{
					if(b < amount_size)
					{
						row.amount[b] = 0;
					}
				}
				else if(a == 3)
				{
					if(b < remarks_size)
					{
						row.remarks[b] = 0;
					}
				}
				a++;
				b = 0;
			}
			prevChar = currentChar;
		}
		while(currentChar != EOF);
		
		if(a != 0 && numRows != 0)
		{
			if(processTransfer(atoi(customerID), customerName, transactionCode,
					atoi(customerAccountId), atoi(row.account_id), row.account_name, 
					strtof(row.amount, 0), row.remarks, mysqlHost, mysqlUser, 
					mysqlPassword, mysqlDatabase))
			{
				result = 1;
			}
			else
			{
				result = 0;
			}	
		}
		
		desctruct_transaction_row(row);
	}
	else
	{
		puts("Error in reading file.");
		return -1;
	}
	fclose(pFile);
	
	if(result == 1)
	{
		return 0;
	}
	else
	{
		return 1;
	}
}
