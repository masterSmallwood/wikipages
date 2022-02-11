###How to run:

Build the image:

`docker build . --tag "top-wiki-pages"`

Run the container:

`docker run -t -d --rm --name wikipages top-wiki-pages `

Exec into the container:

`docker exec -ti wikipages bash`

###Usage:

If you don't provide any arguments, it will query the current date and hour. The wikipedia data for the current hour may not be available yet.

`php main.php`

You can query for a specific date and hour:

`php main.php 2022-02-01 12`

You can query for a range of results:

`php main.php 2022-02-01 12 --end-date=2022-02-02 --end-hour=12` 

Keep in mind, that for any results you have not previously generated, the data for each hour will need to be downloaded before results can be generated. So providing a large range could potentially take a very long time depending on your internet speed.

### Notes:
#### Production Setting Improvements
 TODO