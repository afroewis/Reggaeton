# Reggaeton
A simple PHP Client for MPD (music player daemon). 

It provides basic functionality to play and manage music: 
- Add files to a playlist
- List tracks, genres, albums
- Play, stop, pause music


## Example
Place a file called `example.mp3` into your music database folder. 
```php
// Autoload or require the client
$client = new Client();
$client->update(); // Update the client's music database
$client->add("example.mp3"); // Add the file to the playlist
$client->play(1); // Play the first song in the playlist
sleep(5);
$client->stop();
```

## Prerequisites
You need a healthy MPD installation to use Reggaeton. See https://wiki.archlinux.org/index.php/Music_Player_Daemon for more information about MPD. 
