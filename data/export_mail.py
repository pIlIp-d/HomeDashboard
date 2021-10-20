#!/usr/bin/env python
#python3 required

import smtplib, sys, mimetypes
from email.message import EmailMessage

#init values
message = EmailMessage()
message['Subject'] = 'Home Dashboard Rezept'
message['From'] = ''#your server mail
message['To'] = str(sys.argv[2])
message.set_content(str(sys.argv[1]))
file = "data/"+str(sys.argv[3])

def file_attach(email,filename):
	with open(filename, "rb") as file:
		file_data = file.read()
		maintype, _, subtype = (mimetypes.guess_type(filename)[0] or "application/octet-stream").partition("/")
		email.add_attachment(file_data,maintype=maintype, subtype=subtype, filename=filename)

def sendmail(message):
	server = smtplib.SMTP('mail.gmx.net')#change to your mail-server
	server.starttls()
	server.login('','')#mail,pw
	server.send_message(message)
	server.quit()

file_attach(message,file+".pdf")
#file_attach(message,file+".txt")
sendmail(message)
