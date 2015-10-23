#!/bin/sh
mysql -uroot public_github <<EOF
drop table repos;
EOF
mysql -uroot public_github < repos.sql
