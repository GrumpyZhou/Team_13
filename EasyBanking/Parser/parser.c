#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <mysql.h>

#define EXIT_FAILURE 1
#define BUFFER_SIZE 50
#define TAN_SIZE 16
#define MYSQL_SERVER_ADDRESS "localhost"
#define MYSQL_USER "root"
#define MYSQL_PW "?team13!"
#define MYSQL_DB "bank"

void usage(char **argv)
{
    fprintf(stdout, "usage: %s <sender_id> <tan_id> <tan> <path_to_batch_file>", argv[0]);
    exit(EXIT_FAILURE);
}


int main(int argc, char **argv) {
	//Format: <receiver_id> <amount>
	int sender_id, tan_id, receiver_id, first_space_location = 0;
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
	sender_id = atoi(argv[1]);


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

	sprintf(sql_command, "SELECT * FROM tans WHERE user_id = %d AND tan = '%s' AND tan_id = '%d' AND used = '0'", sender_id, tan, tan_id);
	if (mysql_query(conn, sql_command)) {
		fprintf(stderr, "%s\n", mysql_error(conn));
		exit(1);
	}
	res = mysql_use_result(conn);

	if ((row = mysql_fetch_row(res)) == NULL)
	{
		printf("TAN not available or already used! Exit.");
		mysql_close(conn);
		exit(EXIT_FAILURE);
 	}
	mysql_free_result(res);

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


		 sprintf(sql_command, "SELECT * FROM accounts WHERE user_id = '%d' AND balance > '%f'", sender_id, amount);
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


		 sprintf(sql_command, "UPDATE tans SET used='1' WHERE user_id = '%d' AND tan = '%s' AND tan_id = '%d'", sender_id, tan, tan_id);
		 if (mysql_query(conn, sql_command)) {
			 fprintf(stderr, "%s\n", mysql_error(conn));
			 exit(1);
		 }

		 if (amount <= 10000)
		 {
			 sprintf(sql_command, "INSERT INTO transactions (sender_id, receiver_id, amount, approved) VALUES ('%d', '%d', '%f', '1')", sender_id, receiver_id, amount);
		 }
		 else
		 {
			 sprintf(sql_command, "INSERT INTO transactions (sender_id, receiver_id, amount, approved) VALUES ('%d', '%d', '%f', '0')", sender_id, receiver_id, amount);
		 }
		 if (mysql_query(conn, sql_command)) {
			 fprintf(stderr, "%s\n", mysql_error(conn));
			 exit(1);
		 }


		 if(amount <= 10000)
		 {
			 sprintf(sql_command, "UPDATE accounts SET balance = balance - %f WHERE user_id = '%d'", amount, sender_id);
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
