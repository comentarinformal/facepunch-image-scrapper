# facepunch-image-scrapper
Uses both clientside and serverside scripts to get images from facepunch links (maybe appliable to other vBulletins?) and sends them to a server with PHP, where a reference to their URL is stored. 

User can then categorize those images (downloading them to the server via curl at the same time), or delete them (erasing their reference). 
To make categories, simply make a new folder (lowercase pls), and refresh the page.
Oh yeah, keep in mind $user_that_serves_webs needs to have write access to all the categories (including unsorted)


Client scripts needs to be watched; it doesn't stop on thread finish (going back and forth in the last 2 pages).