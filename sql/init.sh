#!/bin/sh
mysql -uroot public_github <<EOF
drop table if exists repos_log;
drop table if exists users;
drop table if exists pushed_log;
EOF
mysql -uroot public_github < repos_log.sql
mysql -uroot public_github < users.sql
mysql -uroot public_github < pushed_log.sql
