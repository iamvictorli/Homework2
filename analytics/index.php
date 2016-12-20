<?php
define('URL_TO_TRACKER_SITE', 'http://localhost/analytics/index.php');

//starting of HTML page
function print_header() {
echo('<!DOCTYPE html>
    <html>
    <head>
        <title>Web Page Analytics Page</title>
        <link rel="stylesheet" type="text/css" href="analytics.css">
    </head>

    <body>');
}

//end of HTML page
function print_end() {
    echo('</body>
    </html>');
}

if(isset($_REQUEST['activity'])) {
    if($_REQUEST['activity'] === 'codes') {
        codes();
    }
    else if($_REQUEST['activity'] === 'counts') {
        counts();
    }
    else if($_REQUEST['activity'] === 'analytics') {
        analytics();
    }
    else {
        landing();
    }
}
else {
    landing();
}

//landing page
function landing() {
    print_header();

    ?>
    <h1>Web Page Tagging Analytics</h1>
    <form name="activityForm" method="get">
        <input type="text" name="arg" size="30" placeholder="Enter Site Magic String">
        <select name="activity">
            <option value="codes">Get Site Tracker Codes</option>
            <option value="analytics" selected>View Analytics</option>
        </select>
        <input type="submit" name="sendform" value="Go">
    </form>
    <?php

    print_end();
}

//if activity is codes
function codes() {
    if(empty($_REQUEST['arg'] || !isset($_REQUEST['arg']))) {
        landing();
    }
    else {
        if(!isset($_REQUEST['arg2']) && empty($_REQUEST['arg2'])) {
            $arg = $_REQUEST['arg'];
            print_header();
            ?>
            <h1>Tracker Codes - Web Page Tagging Analytics</h1>
            <form>
                <input type="text" name="arg2" size="30" placeholder="Enter a URL to track">
                <input type="submit" name="sendform" value="Go">
                <input type="hidden" name="arg" value="<?php echo($_REQUEST['arg']); ?>">
                <input type="hidden" name="activity" value="<?php echo($_REQUEST['activity']);?>">
            </form>

            <?php
            print_end();
        }
        else {

            print_header();
            $arg = $_REQUEST['arg'];
            $arg2 = $_REQUEST['arg2'];

            $XXXX = sha1($arg . $arg2);
            $YYYY = sha1($arg);

            ?>
            <h1>Tracker Codes - Web Pagging Analytics</h1>
            <form>
                <input type="text" name="arg2" size="30" value="<?php echo($arg2); ?>">
                <input type="submit" name="sendform" value="Go">
                <input type="hidden" name="arg" value="<?php echo($arg); ?>">
                <input type="hidden" name="activity" value="<?php echo($_REQUEST['activity']); ?>">
            </form>

            <h2>Add the following code to the web page of the site with the URL just entered<h2>
            <p>&lt;script src&#61;&quot;<?php echo(URL_TO_TRACKER_SITE) ?>&quest;activity&equals;counts&amp;arg&equals;<?php echo($YYYY) ?>&amp;arg2&equals;<?php echo($XXXX); ?>&quot;&gt;&lt;&sol;script&gt;</p>
            <?php

            print_end();

            //if a file url_lookups exists
            if(file_exists("url_looksups.txt")) {
                $url_lookup = fopen("url_lookups.txt", "r");
                $reading = fread($url_lookup, filesize("url_lookups.txt")); //read url_lookup.txt into $reading
                fclose($url_lookup); //close stream to url_lookup

                $lookups = unserialize($reading); //unserialize reading
                $lookups[$XXXX] = $arg2;

                $serialize = serialize($lookups); //serialize lookups

                $url_lookup = fopen("url_lookups.txt", "w");
                fwrite($url_lookup, $serialize); // write over url_lookup.txt
                fclose($url_lookup);

            }
            else { //when file url_lookups does not exists
                $lookups[$XXXX] = $arg2;
                $serialize = serialize($lookups);

                $url_lookup = fopen("url_lookups.txt", "w");
                fwrite($url_lookup, $serialize);
                fclose($url_lookup);
            }
        }
    }

}

//when activity is counts
function counts() {
    if(isset($_REQUEST['arg']) && isset($_REQUEST['arg2'])
        && !empty($_REQUEST['arg']) && !empty($_REQUEST['arg2'])) {

            $IP = $_SERVER['REMOTE_ADDR'];
            $arg = $_REQUEST['arg'];
            $arg2 = $_REQUEST['arg2'];

            //if counts.txt exists
            if(file_exists('counts.txt')) {
                $countstxt = fopen('counts.txt', 'r'); //read counts.txt
                $reading = fread($countstxt, filesize('counts.txt'));
                fclose($countstxt);

                //unserialize from counts.txt
                $counts = unserialize($reading);
                if(isset($counts[$arg][$arg2][$IP])) {
                    $counts[$arg][$arg2][$IP]++; //increment by 1 if it is set
                }
                else {
                    $counts[$arg][$arg2][$IP] = 1; //set to 1
                }

                $serialize = serialize($counts);
                $countstxt = fopen('counts.txt', 'w'); //write over old counts.txt
                fwrite($countstxt, $serialize);
                fclose($countstxt);
            }
            else {
                //file counts.txt does not exists
                $counts[$arg][$arg2][$IP] = 1; //set new value to 1
                $serialize = serialize($counts);

                $countstxt = fopen('counts.txt', 'w'); //make new file counts.txt
                fwrite($countstxt, $serialize);
                fclose($countstxt);
            }

            echo("tracking = \"done\";");
        }
}

function analytics() {
    if(isset($_REQUEST['arg']) && !empty($_REQUEST['arg'])) {
        if(!file_exists('counts.txt') && !file_exists('url_lookups.txt')) {
            //if either counts.txt or url_Lookups.txt does not exist, go to landing page
            landing();
            return;
        }
        else{
            print_header();
            ?>
            <h1>View Analytics - Web Page Tagging Analytics</h1>
            <h2>Analytics for <?php echo($_REQUEST['arg']); ?></h2>

            <?php

            //variable readingUrl_lookup has file of url_lookup.txt
            $url_lookup = fopen('url_lookups.txt', 'r');
            $readingUrl_lookup = fread($url_lookup, filesize('url_lookups.txt'));
            fclose($url_lookup);

            //variable readingCountsTXT has file of counts.txt
            $countstxt = fopen('counts.txt', 'r');
            $readingCountsTXT = fread($countstxt, filesize('counts.txt'));
            fclose($countstxt);

            //loops through $counts[$YYYY] which contains URLS and IPs counted
            //$XXXX is the sha1 encryption of magic string concatenated with the website URL
            //$IP is the ip addresses that has visiited the page
            $YYYY = sha1($_REQUEST['arg']);
            $lookups = unserialize($readingUrl_lookup);
            $counts = unserialize($readingCountsTXT);

            foreach ($counts[$YYYY] as $XXXX => $IP) {
                $url = $lookups[$XXXX];

                //total number of hits
                $totalHits = array_sum($counts[$YYYY][$XXXX]);
                ?>
                <h3><?php echo($url); ?> (total hits: <?php echo($totalHits); ?>)</h3>
                <table>
                    <tr>
                        <th>IP Address</th>
                        <th>Hits</th>
                    </tr>
                <?php

                foreach($counts[$YYYY][$XXXX] as $IPAdress => $hits) {
                    ?>
                    <tr>
                        <td><?php echo($IPAdress); ?></td>
                        <td><?php echo($hits); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
            }
        }
    }
}
