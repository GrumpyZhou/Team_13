# Team_13
Secure Coding, winter term 2015/2016.

## Server requesities
Additionally to the provided software, the following apt-get packages have to be installed (sudo apt-get install [package-name]):
* ssmtp
* libmysqlclient-dev

Also, the following lines have to be inserted into /etc/ssmtp/ssmtp.conf:

```bash
mailhub=smtp.gmail.com:587
UseSTARTTLS=YES
AuthUser=easybanking1313@gmail.com
AuthPass=ttest123
FromLineOverride=YES
```

To compile the Batch Parser, cd into the Parser source code directory and execute make.
