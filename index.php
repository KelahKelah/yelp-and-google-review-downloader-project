<?php
// error_reporting(0);
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
  if (isset($_POST["yelp_search"])) {
    if (isset($_POST["search"]) && !empty($_POST["search"])) { 
      $url1 = filter_var($_POST["search"], FILTER_SANITIZE_URL);
        if (filter_var($url1, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) { //validate URL File path must contain Http://

          $url_split = parse_url($url1);
            $host = explode('/biz/',$url_split['path']);// split the url here,get the part after /biz/
            $host = implode("",$host);
            // print_r($url_split);exit();
            
            $API_KEY = 'Lf99pUNaS1Xy9yY6bByyaJo_Fmh_bxO3oqMVcfNUmqypzQkfWBhQPryJd3WBfU4vJiNaFB6-vi1vucCJW7rktYJ9PvpkNGCH29FJMq4nHM5i5ZfngbTLjcABMUGRXHYx';
            // Complain if credentials haven't been filled out.
            assert('$API_KEY', "Please supply your API key.");
            $API_HOST = "https://api.yelp.com";
            $BUSINESS_PATH = "/v3/businesses/";  // Business ID after slash.
            $REVIEW_PATH = "/reviews"; // path for querying review API


            //Makes a request to the Yelp API and returns the response
            //@param    $host    The domain host of the API 
            //@param    $path    The path of the API after the domain.
            //@param    $url_params    Array of query-string parameters.
            //@return   The JSON response from the request      

            function request($host, $path, $url_params = array()) {
                // Send Yelp API Call
              try {
                $curl = curl_init();
                if (FALSE === $curl)
                  throw new Exception('Failed to initialize');
                $url = $host . $path . "?" . http_build_query($url_params);
                curl_setopt_array($curl, array(
                  CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true,  // Capture response.
                        CURLOPT_ENCODING => "",  // Accept gzip/deflate/whatever.
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => array(
                          "authorization: Bearer " . $GLOBALS['API_KEY'],
                          "cache-control: no-cache",
                        ),
                      ));
                $response = curl_exec($curl);
                if (FALSE === $response)
                  throw new Exception(curl_error($curl), curl_errno($curl));
                $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                if (200 != $http_status)
                  throw new Exception($response, $http_status);
                curl_close($curl);
              } catch(Exception $e) {
                trigger_error(sprintf(
                  'Curl failed with error #%d: %s',
                  $e->getCode(), $e->getMessage()),
                E_USER_ERROR);
              }
              return $response;
            }
            
            //Query the Reviews API by business_id

            //@param    $business_id    The ID of the business to query
            //@return   The JSON response from the request 
            
            function get_review($review_alias) {
              $review = $GLOBALS['BUSINESS_PATH'] . $review_alias . $GLOBALS['REVIEW_PATH']; 
              return request($GLOBALS['API_HOST'], $review);
            }

            // User input is handled here 
            
            $business_alias = $host; 
            $see_review = get_review($business_alias);
            if ($see_review) {
              $result = "Result for yelp business search : <b>$url1</b>. <br><br> Note that the Yelp API only allows for 3 reviews per call."; 
              $review_response = json_decode($see_review, TRUE);
            // header('Content-Type: application/json');
            // print_r($review_response)."\n";

            }
          } else {
            $result = "Not a valid link. URL must contain the protocol: HTTP";
          }

        }
    } elseif (isset($_POST["google_search"])) { 
        exit("google url path too long"); 
    }else {
      exit('error');
    }
} 
  // }
?>

<!DOCTYPE html>
<html lang="en">
<head>
          <!-- Required meta tags -->
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
          <title> Yelp and google review search </title>   

          <!-- Bootstrap CSS -->
          <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
 <style>
            alert{
                
        }
            container {
            padding: 7px 12px;
            border: 1px solid #4285f4;
            font-weight: bold;
            color: white;
            background: #4285f4;
            width: 400px;
            text-align: center
        }
            button[type="submit"] {
            font-size: 13px;
            font-weight: bold;
            margin: 18px 4px 11px;
            min-width: 54px;
            padding: 5px 16px;
            text-align: center;
            border-radius: 2px;
            height: 36px;
            line-height: 27px;
            border: 1px solid #fff;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
            color: #222;
            background-color: #f2f2f2;
            cursor: pointer;
        }
</style>        
</head>
<body>

          <div class="container" style="margin:auto; max-width:500px; margin-top: 100px;">

            <?php
            if (isset($result)) {
             echo '<div class="alert alert-danger text-center">
             <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
             <p class="text-size-16">'.$result.'</p>
             </div>';
           }
           ?>
        <h4> Want to view customers review? </h4><br><br>
           <form action="index.php" method="POST" role="form"> 

            <div class="form-group">
              <label for="">Enter url</label>
              <input type="text" name="search" class="form-control" placeholder="Input url">
            </div>  

            <button type="submit" class="btn btn-primary" name="yelp_search">Yelp Search</button>
            <button type="submit" class="btn btn-primary" name="google_search">Google Search</button>

          </form>
        </div>      
        <?php if (isset($review_response)&&!empty($review_response)) { 
          echo "<div class=\"card-body\">";
            // echo "<button type=\"button\" class=\"getfile btn btn-danger btn-lg push\">Download</button>";
          echo "<br><br>";
          echo "<div class=\"table-responsive\" style=\"overflow-x:auto;\">";
          echo "<table class=\"table table-striped table-bordered table-hover\">";
          echo "<thead  class=\"thead-dark\">";
          echo "<tr>";
          echo "<th scope=\"col\">Name</th>";
          echo "<th scope=\"col\">Date</th>";
          echo "<th scope=\"col\">Rating</th>";
          echo "<th scope=\"col\">Review</th>";
          echo "<th scope=\"col\">URL</th>";
          echo "</tr>";
          echo "</thead>";
          echo "<tbody>";
          $header = array("Name","Date","Rating","Review","URL");
          $file = 'yelp_review' .time().'.csv';
          $fp = fopen($file, 'w');
          fputcsv ($fp, $header);
                    // print_r($review_response);
          foreach ($review_response as $review_responses){
            if (is_array($review_responses)) {
              foreach ($review_responses as $key) {
                if (is_array($key)) {
                  echo "<tr>";
                  echo "<td>" . $key['user']['name'] . "</td>";
                  echo "<td>" . $key['time_created'] . "</td>";
                  echo "<td>" . $key['rating'] . "</td>";
                  echo "<td>" . $key['text'] . "</td>";
                  echo "<td style=\"word-wrap: break-word;min-width: 500px;max-width: 500px;\">" . $key['url'] . "</td>";
                  echo "</tr>";
                  $array_for_csv = array($key['user']['name'], $key['time_created'], $key['rating'], $key['text'], $key['url'] );
                  fputcsv ($fp, $array_for_csv);
                } 
              } 
            } 
          } 
          fclose($fp);
        } 
      // }
      echo "</tbody>";
      echo "</table>";
      echo "</div>";
      echo "</div>";
      ?>

      <!-- Optional JavaScript -->
      <!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
</body>
</html>


