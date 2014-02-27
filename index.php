<?php
session_start();

// Load the config and the system object
require_once('config.php');
require_once('lib'.DS.'System.php');

// Define the autoload stuffs
function __autoload($class){
    include "lib/$class.php";
}

// Instantiate system and check config options
$sys = new System();

if(!empty($_GET['token']) && empty($_SESSION['token'])){
    $_SESSION['token'] = $_GET['token'];
}

if(!empty($_GET['secret']) && empty($_SESSION['secret'])){
    $_SESSION['secret'] = $_GET['secret'];
}

if(!empty($_SESSION['token']) && !empty($_SESSION['secret'])){
    $token = $_SESSION['token'];
    $secret = $_SESSION['secret'];

    // Check for errors in the config
    if(!$sys->configErrorCheck()){
        $sirportly_api = new SirportlyAPI($token, $secret);

        $these_are_the_fields_youre_after = array();
        $these_are_the_tickets_youre_after = array();
        $these_are_the_statuses_youre_after = array();

        $spql = !empty($_GET['spql']) ? $_GET['spql'] : null;

        if($spql && $all_tickets = $sirportly_api->execSPQL($spql)){

            if(!array_search($spql, $_SESSION['queries'])){
                $_SESSION['queries'][] = $spql."&token=$token&secret=$secret";
            }

            if(!empty($all_tickets->fields)){
                foreach($all_tickets->fields as $field){
                    $these_are_the_fields_youre_after[] = $field;
                }
            } else {
                $msgs[] = "No fields found";
            }

            if(!empty($all_tickets->results)){
                foreach($all_tickets->results as $ticket){
                    $these_are_the_tickets_youre_after[] = $ticket;
                }
            } else {
                $msgs[] = "No ticket records found";
            }
        } else {
            $msgs[] = "No tickets found";
        }
    } else {
        $msgs[] = "Config error";
    }

    $minitemplates = array(
        'tickets.reference' => '<a href="'.SIRPORTLY_URL.'/staff/tickets/{val}" target="_blank">{val}</a>',
    );
} else {
    $msgs[] = "Missing token or secret";
}
?>

<html>
<head>
    <title>SPQL Exec</title>

    <style>
        * {
            padding: 0;
            margin: 0;
        }
        body{
            font-family: helvetica, arial;
            font-size: 12px;
            text-align: center;
            padding: 10px;
        }
        h1 {
            margin: 10px 0 20px;
        }
        h2 {
            margin: 30px 0 20px;
        }
        p {
            margin: 10px 0;
        }
        textarea {
            width: 90%;
            height: 100px;
            font-size: inherit;
            font-family: inherit;
            padding: 5px;
            border: 1px solid black;
        }
        input[type=text] {
            font-size: inherit;
            font-family: inherit;
            padding: 5px;
            border: 1px solid black;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: inherit;
            font-family: inherit;
        }
        th, td {
            padding: 2px;
        }
        td {
            text-align: left;
            border: 1px solid grey;
        }
        tr:nth-child(even) {
            background-color: #fbfbfe;
        }
        #footer {
            margin: 20px 0;
            font-size: 0.8em;
        }
        .tleft {
            text-align: left;
        }
        .tright {
            text-align: right;
        }
        form div {
            margin: 10px 0 0;
        }
    </style>
</head>

<body>

<h1>Sirportly Query Executioner</h1>

<p>
    <a href="http://sirportly.com/docs/reporting/the-sirportly-query-language">The Sirportly Query Language documentation</a>
</p>

<form>
    <div>
        Token: &nbsp; <input type="text" name="token" placeholder="Token" value="<?php echo isset($token) ? $token : ''; ?>" />
        &nbsp; Secret: &nbsp; <input type="text" name="secret" placeholder="Secret" value="<?php echo isset($secret) ? $secret : ''; ?>" />
    </div>
    <div>
        <textarea name="spql"><?php echo $_GET['spql']; ?></textarea>
    </div>
    <div>
        <input type="submit" value="Run">
    </div>
</form>

<h2>Results</h2>
<?php
if(!empty($these_are_the_tickets_youre_after) && !empty($these_are_the_fields_youre_after)){

    echo '<p class="tright">Num records found: '.count($these_are_the_tickets_youre_after).'</p>';
    echo '<table>';
    echo '<tr>';

    foreach($these_are_the_fields_youre_after as $field){
        echo "<th>".ucwords(array_pop(explode('.', $field)))."</th>";
    }

    echo '</tr>';

    foreach($these_are_the_tickets_youre_after as $ticket){
        echo '<tr>';
        foreach($ticket as $key => $ticket_field){

            if(!empty($minitemplates[$these_are_the_fields_youre_after[$key]])){
                echo "<td>".str_replace('{val}', $ticket_field, $minitemplates[$these_are_the_fields_youre_after[$key]])."</td>";
            } else {
                echo "<td>$ticket_field</td>";
            }
        }
        echo '</tr>';
    }

    echo '</table>';
} else {
    echo "<p>No results returned</p>";
}

if(!empty($_SESSION['queries']) && is_array($_SESSION['queries'])){
    echo "<h2>Previous 10 Queries</h2>";

    // Limit (to 10) and flip the queries session array
    $queries = array_reverse(array_slice($_SESSION['queries'], -10, 10, true));
    foreach($queries as $query){
        echo "<p class=\"tleft\"><a href=\"?spql=$query\">$query</a></p>";
    }
}

if(isset($_GET['debug'])){
?>

<h2>Debug Messages</h2>

<?php
    if(!empty($msgs) && is_array($msgs)){
        echo '<p>'.implode('</p><p>', $msgs).'</p>';
    } else {
        echo '<p>None</p>';
    }
}
?>

<div id="footer">By jadams@wearefolk.com</div>

</body>
</html>