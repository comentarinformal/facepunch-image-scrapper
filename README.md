# facepunch-image-scrapper
Uses both clientside and serverside scripts to get images from facepunch links (maybe appliable to other vBulletins?) and sends them to a server with PHP, where a reference to their URL is stored. 

User can then categorize those images (downloading them to the server via curl at the same time), or delete them (erasing their reference). 

Client scripts needs to be taken care of; it doesn't stop on thread finish (going back and forth in the last 2 pages).
