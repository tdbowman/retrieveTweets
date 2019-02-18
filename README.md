# retrieveTweets
The first script allows someone to retrieve 100 tweets per call to Twitter API using a known tweet ID. It was developed in order to retreive the tweet object from the Twitter API using tweet IDs collected by Altmetric.com. The second script parses the JSON files and inserts the tweet object data into a relational MySQL database.

## Getting Started
To use the script, you will need to have both PHP 7.x and MySQL installed and running on a web server. You will also need to create a MySQL database with tables using the .sql files in the MySQL folder.

### Prerequisites
- You need to have PHP v7.x installed.
- You need to have the latest version of MySQL installed
- You need Twitter API keys to access the Twitter API
- You will need space to store the JSON files on the server (space size depends on number of tweets you are retrieving)

## Installing
You will need to:
1. Execute the 'tables.sql' file in your MySQL database; this will create the tables you need for storing of tweet data
1a. The assumption is that you have tweet IDs to collect stored in the newly created 'altmetric_twitter_keys' table.
2. Ensure you have all the files installed on a web server where you can execute PHP
3. You can run `nohup php retrieve_100Tweets_usingTweetID.php > logFile.log &` from a terminal on the server when you are in the directory where this file is located.  The `nohup` command will allow you to run the script in the background.
4. Once the retrieval of your tweet objects is finished, you can run the parse file.
5. You can run `nohup php parseJSON_tweets_from_TwitterAPI.php > parseFile.log &` from a terminal on the server when you are in the directory where this file is located. The `nohup` command will allow you to run the script in the background.
6. If all goes well, you will have all your tweets parsed and entered into the relational MySQL db.


## Built With

* [twitteroauth](https://github.com/abraham/twitteroauth) - Twitter Oauth


## Authors

* **Timothy D Bowman** - *Initial work* - [tdbowman.com](https://www.tdbowman.com/)


## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

