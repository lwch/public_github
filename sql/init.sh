#!/bin/sh
mysql -uroot public_github <<EOF
drop table if exists repos_log;
drop table if exists users;
EOF
mysql -uroot public_github < repos_log.sql
mysql -uroot public_github < users.sql
