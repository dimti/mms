#!/bin/bash
QUICK_DIR=quick

cd $QUICK_DIR &&
find ./ -name "*.sh.example" -exec sh -c 'F={}; cp -u $F ${F%.example} && chmod +x ${F%.example}' \;
