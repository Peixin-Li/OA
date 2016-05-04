#!/usr/bin/expect -f 
#########################################################################
# File Name: ad_pwd.sh
# Author: root
# mail: root@debian.com
# Created Time: Thu 31 Dec 2015 06:09:17 PM CST
########################################################################

set USER [lindex $argv 0]
set OLD_PWD [lindex $argv 1]
set NEW_PWD [lindex $argv 2]

spawn smbpasswd -r ad.i.shanyougame.com -U $USER
expect {
"Old SMB password:" {send "$OLD_PWD\n"; exp_continue}
"New SMB password:" {send "$NEW_PWD\n"; exp_continue}
"Retype new SMB password:" {send "$NEW_PWD\n"; exp_continue}
}
interact
