<?php

    include('simple_html_dom.php');

    interface iRadovi
    {
        public function create($naziv, $tekst, $link, $oib);
        public function save();
        public function read($template);
    }

    function connectToDatabase() {

        $host = 'localhost';
        $username = 'root';
        $password = '';
        $database = 'radovi';

        // Create a new MySQLi object
        $mysqli = new mysqli($host, $username, $password, $database);
    
        // Check if there was an error connecting to the database
        if ($mysqli->connect_error) {
            die("Failed to connect to MySQL: " . $mysqli->connect_error);
        }
    
        // Return the MySQLi object
        return $mysqli;
    }

    class DiplomskiRadovi implements iRadovi {

        private $naziv_rada;
        private $tekst_rada;
        private $link_rada; // https://stup.ferit.hr/index.php/zavrsni-radovi/page/2
        private $oib_tvrtke;

        function __construct() {
        }

        public function create($naziv, $tekst, $link, $oib) {
            $this->naziv_rada = $naziv;
            $this->tekst_rada = $tekst;
            $this->link_rada = $link;
            $this->oib_tvrtke = $oib;
        }

        public function save() {
            $connection = connectToDatabase();

            $query = "INSERT INTO `diplomski_radovi` (`naziv_rada`, `tekst_rada`, `link_rada`, `oib_tvrtke`) VALUES ('$this->naziv_rada', '$this->tekst_rada', '$this->link_rada', '$this->oib_tvrtke')";

            $connection->query($query);

            $connection->close();
        }

        public function read($template) {
            $connection = connectToDatabase();
            $query = "SELECT * FROM `diplomski_radovi`";

            $result = $connection->query($query);

            // Check if the query was successful
            if ($result) {
                // Fetch the row as an associative array
                $row = $result->fetch_assoc();

                // Do something with the row data
                echo "Naslov: " . $row["naslov_rada"] . "<br>";
                echo "Tekst: " . $row["tekst_rada"] . "<br>";
                echo "Link: " . $row["link_rada"] . "<br>";
                echo "OIB: " . $row["oib_rada"] . "<br>";
            } else {
                // Handle the error
                echo "Error executing query: " . $connection->error;
            }

            $connection->close();   

        }
    }

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://stup.ferit.hr/index.php/zavrsni-radovi/page/2");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);
    //var_dump($response);

    $html = str_get_html($response);

    foreach($html->find('article') as $article) {

        $naziv = $article->find('h2.entry-title a')->plaintext;
        $link = $article->find('h2.entry-title a')->href;
        $tekst = $article->find('div.fusion-post-content-container p')->plaintext;

        $image = $article->find('img', 0);
        $image_src = $image->src;
        $oib = substr($image_src, strrpos($image_src, '/') + 1, -4);

        $dimplomski = new DiplomskiRadovi();
        $dimplomski->create($naziv, $test, $link, $oib);
        $dimplomski->save();
    }
    fclose($fp);

?>