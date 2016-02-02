#!/bin/sh
while true; do
  file=$(inotifywait -r --format "%w" -e create -e delete ./*)
  python img.py $file
done