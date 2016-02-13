#!/bin/sh
while true; do
  file=$(inotifywait -r --format "%w%f" -e create -e delete ./*)
  mogrify -resize '800x600>' $file
  python img.py $file
done