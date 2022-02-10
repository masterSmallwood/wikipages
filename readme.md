###How to run:

Build the image:

`docker build . --tag "top-wiki-pages"`

Run the container:

`docker run -t -d --rm --name wikipages top-wiki-pages `

Exec into the container:

`docker exec -ti wikipages bash`

Run the program:

`php main.php`