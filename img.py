from os import listdir
from os.path import isfile, join
import json
from time import gmtime, strftime
import sys

def init_json(path, name, date):
  data = {
    "@context" : {
         "as2" : "http://www.w3.org/ns/activitystreams#"
        ,"col" : "http://ns.jasnell.me/socialwg#"
        ,"dc" : "http://purl.org/dc/elements/1.1/"
        ,"img" : "http://img.amy.gy/v#"
      },
    "@id" : "http://img.amy.gy/%s" % path,
    "@type" : ["as2:Collection", "col:Album"],
    "as2:name" : name,
    "as2:published" : date,
    "dc:creator" : {"@id" : "http://rhiaro.co.uk/about#me"},
    "as2:items" : []
  }
  
  files = [f for f in listdir(path) if isfile(join(path, f))]
  for file in files:
    data["as2:items"].append({ "@id" : "http://img.amy.gy/files/%s/%s" % (path, file), "as2:name" : "" })
    
  return data
  
def update_items(path):
  with open('%s/%s.json' % (path, path)) as data_file:
      data = json.load(data_file)
  files = [f for f in listdir(path) if isfile(join(path, f))]
  for file in files:
    if file[-5:] != '.json' and not any([("@id", "http://img.amy.gy/files/%s/%s" % (path, file)) in d.items() for d in data["as2:items"]]):
      data["as2:items"].append({ "@id" : "http://img.amy.gy/files/%s/%s" % (path, file), "as2:name" : "" })
    
  return data

def main(fullpath):
  now = strftime("%Y-%m-%dT%H:%M:%S")
  fullpath = fullpath.split('/')
  path = fullpath[len(fullpath)-2]
  try:
    data = update_items(path)
    print 'updated'
  except IOError:
    data = init_json(path, path, now)
    print 'new'
  
  with open('%s/%s.json' % (path, path), 'w') as file:
    file.write(json.dumps(data, indent=4, separators=(',', ': ')))

main(sys.argv[1])