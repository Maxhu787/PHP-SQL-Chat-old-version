<?php
session_start();
require_once "pdo.php";
date_default_timezone_set('UTC');

if (!isset($_SESSION["email"])) {
  echo "PLEASE LOGIN";
  echo "<br />";
  echo "Redirecting in 3 seconds";
  header("refresh:3;url=index.php");
  die();
}

$stmt = $pdo->query(
  "SELECT * FROM chatlog"
);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (isset($_POST['reset'])) {
  $_SESSION['chats'] = array();
  header("Location: chat.php");
  return;
}
if (isset($_POST['message'])) {
  $stmta = $pdo->prepare(
    'INSERT INTO chatlog
  (message, message_date, account)
  VALUES (:msg, :msgd, :acc)'
  );

  $stmta->execute(
    array(
      ':msg' => $_POST['message'],
      ':msgd' => date(DATE_RFC2822),
      ':acc' => $_SESSION['email']
    )
  );
  $stmt = $pdo->query(
    "SELECT * FROM chatlog"
  );
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<html>
<title>g4o2 chat</title>
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0">
<style type="text/css">
  @import url('https://fonts.googleapis.com/css2?family=Alumni+Sans+Pinstripe&family=Montserrat:wght@300&family=Orbitron&family=Work+Sans:wght@300&display=swap');

  body {
    font-family: Arial, Helvetica, sans-serif;
    background-color: #121212;
    color: #ffffff;
    opacity: 87%;
    overflow-x: hidden;
  }

  #chatcontent {
    height: 40vh;
    width: 97vw;
    overflow: auto;
    border: solid 5px #353935;
  }


  #chatcontent::-webkit-scrollbar-track {
    -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
    background-color: #353935
      /*#F5F5F5*/
    ;
  }

  #chatcontent::-webkit-scrollbar {
    width: 10px;
    background-color: #F5F5F5;
  }

  #chatcontent::-webkit-scrollbar-thumb {
    background-color: #F90;
    background-image: -webkit-linear-gradient(45deg,
        rgba(255, 255, 255, .2) 25%,
        transparent 25%,
        transparent 50%,
        rgba(255, 255, 255, .2) 50%,
        rgba(255, 255, 255, .2) 75%,
        transparent 75%,
        transparent)
  }


  .stats {
    font-size: 12px;
    color: orange;
    margin-left: 10px;
    font-family: Arial, Helvetica, sans-serif;
  }

  .msg {
    color: white;
    font-weight: 100;
    font-size: 16px;
    margin-left: 10px;
  }

  .account {
    cursor: pointer;
    font-size: 15px;
    font-family: monospace, sans-serif;
  }

  #message-input {
    height: 36px;
    font-size: 14px;
    width: 70%;
    margin-left: 2px;
    padding-left: 12px;
    border-radius: 5px;
    border: none;
    background-color: #d1d1d1;
    transition: all .2s ease-in-out;
  }

  #message-input:focus {
    outline: none !important;
    border: 3px solid #ffa500;
    box-shadow: 0 0 0px #719ECE
  }

  #page-header {
    margin-top: -1.7%;
    margin-left: 0.3%;
  }

  h1 {
    font-family: 'Orbitron', arial;
    color: orange;
    font-size: 8vw;
    text-transform: uppercase;
    user-select: none;
  }

  #guide {
    height: 20vh;
    width: 52.4vw;
    margin-top: -7.3%;
    margin-bottom: 1.5%;
    background-color: #343434;
    border-radius: 10px;
    padding: 10px;
  }

  ::placeholder {
    padding-left: 0px;
  }

  :-ms-input-placeholder {
    padding-left: 0px;
  }

  #form {
    margin-top: 1.3%;
  }

  #submit {}

  #reset {}

  .button {
    height: 35px;
    font-size: 16px;
    width: 10%;
    border: none;
    cursor: pointer;
    border-radius: 3px;
    padding: 8px;
    color: orange;
    background-color: #343434;
    transition: all .2s ease-in-out;
  }

  .button:hover {
    background-color: #121212;
    transition: all .2s ease-in-out;
    color: #ffa500;
  }

  .spinner {
    margin-left: 20%;
    margin-right: 20%;
    margin-top: 7vh;
    width: 10vw;
  }


  .rainbow_text_animated {
    background: linear-gradient(to right, #6666ff, #0099ff, #00ff00, #ff3399, #6666ff);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    animation: rainbow_animation 6s ease-in-out infinite;
    background-size: 400% 100%;
  }

  @keyframes rainbow_animation {

    0%,
    100% {
      background-position: 0 0;
    }

    50% {
      background-position: 100% 0;
    }
  }
</style>
</head>

<body>
  <section id="page-header">
    <h1>g4o2&nbsp;chat</h1>
    <section id="guide">
      <p>Press <kbd>Enter</kbd> to submit message</p>
      <p>Press <kbd>Esc</kbd> to deselect</p>
      <p>Press <kbd>/</kbd> to select </p>
    </section>
  </section>
  <section>
    <div id="chatcontent">
      <!--<img class="spinner" src="spinner.gif" alt="Loading..." />-->

      <p class="msg"></p>
      <?php
      if (count($rows) > 0) {
        foreach ($rows as $row) {
          echo ("<p class='stats'>");
          $user = "<a class='account rainbow_text_animated'>" . ucfirst(explode("@", $row['account'])[0]) . "</a>";

          if (isset($_COOKIE['timezone'])) {
            $timezone_offset_minutes = $_COOKIE['timezone'];
            $time = new DateTime($row["message_date"]);
            $minutes_to_add = ($timezone_offset_minutes);
            $time->add(new DateInterval('PT' . $minutes_to_add . 'M'));
            $stamp = $time->format('D, d M Y H:i:s');
          } else {
            $stamp = $row["message_date"];
          }
          echo ($user . " (" . $stamp . ")");
          echo ("</p>");
          
          echo ("<p class='msg'>");
          echo htmlentities($row['message']);
          echo ("</p>");
        }
      }
      ?>
    </div>
    <form id='form' autocomplete="off" method="post" action="chat.php">
      <div>
        <input pattern=".{1,}" required title="3 characters minimum" id='message-input' type="text" name="message" size="60" placeholder="Enter message and submit" />
        <input class='button' id="submit" type="submit" value="Chat" />
        <input class='button' id='reset' type="submit" name="reset" value="Reset" />
      </div>
    </form>
  </section>
  <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.js">
  </script>
  <script type="text/javascript">
    let input = document.getElementById('message-input');
    input.focus();
    input.select();
    let pageBody = document.getElementsByTagName('body')[0];
    window.addEventListener("keydown", event => {
      if ((event.keyCode == 191)) {
        if (input === document.activeElement) {
          return;
        } else {
          input.focus();
          input.select();
          event.preventDefault();
        }
      }
      if ((event.keyCode == 27)) {
        if (input === document.activeElement) {
          document.activeElement.blur();
          window.focus();
          event.preventDefault();
        }
      }
    });


    function chatScroll() {
      let chat = document.getElementById('chatcontent')
      chat.scrollTop = chat.scrollHeight;
    }
    chatScroll()

    if (window.history.replaceState) {
      window.history.replaceState(null, null, window.location.href);
    }

    var timezone_offset_minutes = new Date().getTimezoneOffset();
    timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;

    document.cookie = "timezone=" + timezone_offset_minutes;
  </script>
</body>