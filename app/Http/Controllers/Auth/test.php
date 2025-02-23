<?php
$ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo?prettyPrint=false');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'cURL error: ' . curl_error($ch);
} else {
    echo "Success! Google API response: " . $result;
}

curl_close($ch);
?>
