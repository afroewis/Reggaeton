# Reggaeton
A PHP Client for MPD (music player daemon).

Reggaeton is the right tool to play music using PHP at your next party! It provides basic functionality to play and manage music: 
- Add files to your playlist
- List all tracks/genres/albums/...
- Play, stop, pause music


## Example
Place a file called `example.mp3` into your music database folder. 
```php
// Autoload or require the client
$client = new Client();
$client->update(); // Update the client's music database
$client->add("example.mp3"); // Add the file to the plylist
$client->play(1); // Play the first song in the database
sleep(5);
$client->stop();
```

## Prerequisites
Obviously you'll need a healthy MPD installation to use Reggaeton. See https://wiki.archlinux.org/index.php/Music_Player_Daemon for more information about MPD. 
