#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <mysql.h>

#define EXIT_FAILURE 1
#define BUFFER_SIZE 50
#define TAN_SIZE 16
#define MYSQL_SERVER_ADDRESS "localhost"
#define MYSQL_USER "root"
#define MYSQL_PW "pw"
#define MYSQL_DB "db"

void usage(char **argv)
{
    fprintf(stdout, "usage: %s <sender_id> <path_to_batch_file>", argv[0]);
    exit(EXIT_FAILURE);
}


int main(int argc, char **argv) {
	//Format: <recipient_id> <amount> <tan>
	int sender_id, recipient_id, first_space_location = 0, second_space_location = 0;
	double amount;
	char tan[TAN_SIZE];
	FILE *batch_file;
	char line_buffer[BUFFER_SIZE];
	char *amount_position;
	char *tan_position;

	if(argc != 3)
	{
		usage(argv);
	}
	sender_id = atoi(argv[1]);


	batch_file = fopen (argv[2],"r");
	if (batch_file == NULL)
	{
		printf("Batch file could not be opened. Exit.");
		exit(EXIT_FAILURE);
	}

	while(fgets(line_buffer, sizeof(line_buffer), batch_file) != NULL)
	{
		recipient_id = atoi(line_buffer);

		for(first_space_location = 0; first_space_location < BUFFER_SIZE && line_buffer[first_space_location] != ' '; first_space_location += 1);
		//+1 to jump over space
		amount_position = line_buffer + first_space_location + 1;
		amount = atof(amount_position);

		for(second_space_location = first_space_location + 1; second_space_location < BUFFER_SIZE && line_buffer[second_space_location] != ' '; second_space_location += 1);
		tan_position = line_buffer + second_space_location + 1;
		memcpy(tan, tan_position, 15);
		tan[15] = '\0';
		printf("Current line: %d - %f - %s\n", recipient_id, amount, tan);

		//1. recipient exists? 2. tan valid? 3. confirmation required? 4. add transaction 5. if no confirmation: change balances
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

		 sprintf(sql_command, "SELECT * FROM users WHERE id = %d", recipient_id);
		 if (mysql_query(conn, sql_command)) {
			 fprintf(stderr, "%s\n", mysql_error(conn));
			 exit(1);
		 }
		 res = mysql_use_result(conn);

		 if ((row = mysql_fetch_row(res)) == NULL)
		 {
			 printf("Recipient does not exist! Exit.");
			 exit(EXIT_FAILURE);
		 }
		 mysql_free_result(res);

		 sprintf(sql_command, "SELECT * FROM users WHERE id = %d AND balance > %d", sender_id, amount);
		 if (mysql_query(conn, sql_command)) {
			 fprintf(stderr, "%s\n", mysql_error(conn));
			 exit(1);
		 }
		 res = mysql_use_result(conn);

		 if ((row = mysql_fetch_row(res)) == NULL)
		 {
			 printf("Balance not sufficient! Exit.");
			 exit(EXIT_FAILURE);
		 }
		 mysql_free_result(res);

		 sprintf(sql_command, "SELECT * FROM tans WHERE user_id = %d AND tan = %s AND used = 0", sender_id, tan);
		 if (mysql_query(conn, sql_command)) {
			 fprintf(stderr, "%s\n", mysql_error(conn));
			 exit(1);
		 }
		 res = mysql_use_result(conn);

		 if ((row = mysql_fetch_row(res)) == NULL)
		 {
			 printf("TAN not available or already used! Exit.");
			 exit(EXIT_FAILURE);
		 }
		 mysql_free_result(res);

		 sprintf(sql_command, "UPDATE tans SET used='1' WHERE user_id = %d AND tan = %s", sender_id, tan);
		 if (mysql_query(conn, sql_command)) {
			 fprintf(stderr, "%s\n", mysql_error(conn));
			 exit(1);
		 }

		 mysql_free_result(res);

		 if (amount < 10000)
		 {
			 sprintf(sql_command, "INSERT INTO transactions (sender_id, receiver_id, amount, approved) VALUES ('%d', '%d', '%f', '1')", sender_id, recipient_id, amount);
		 }
		 else
		 {
			 sprintf(sql_command, "INSERT INTO transactions (sender_id, receiver_id, amount, approved) VALUES ('%d', '%d', '%f', '0')", sender_id, recipient_id, amount);
		 }
		 if (mysql_query(conn, sql_command)) {
			 fprintf(stderr, "%s\n", mysql_error(conn));
			 exit(1);
		 }

		 mysql_free_result(res);

		 if(amount < 10000)
		 {
			 sprintf(sql_command, "UPDATE accounts SET balance = balance - %f WHERE user_id = %d", amount, sender_id);
			 if (mysql_query(conn, sql_command)) {
				 fprintf(stderr, "%s\n", mysql_error(conn));
				 exit(1);
			 }

			 mysql_free_result(res);

			 sprintf(sql_command, "UPDATE accounts SET balance = balance + %f WHERE user_id = %d", amount, recipient_id);
			 if (mysql_query(conn, sql_command)) {
				 fprintf(stderr, "%s\n", mysql_error(conn));
				 exit(1);
			 }

			 mysql_free_result(res);
		 }

		 mysql_close(conn);
	}

	fclose (batch_file);

	return 0;
}