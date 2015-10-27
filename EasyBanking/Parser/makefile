all: parser
parser: parser.o
	 gcc -o parser parser.o `mysql_config --libs`

parser.o: parser.c
	 gcc -c `mysql_config --cflags` parser.c
     
clean:
	 rm parser.o parser 
