<?

// Exit if accessed directly
if(!defined('ABSPATH')) exit;

class ActionKit {
    function __construct()
    {
        $this->credentials = json_decode(get_option('ak_credentials'), true);

        $this->paths = array(
            'domain' => 'https://act.demandprogress.org',
            'rest'   => '/rest/v1/',
        );
    }

    function clean_path($path)
    {
        // Remove leading and trailing slashes
        $path = preg_replace('%^/%', '', $path);
        $path = preg_replace('%/$%', '', $path);
        return $path;
    }

    function query($sql, $multiple = false)
    {
        $mysqli = new mysqli(
            'demandprogress.client-db.actionkit.com',
            $this->credentials['db_user'],
            $this->credentials['db_pass'],
            'ak_dprogress'
        );

        if ($mysqli->connect_errno) {
            return array(
                'success' => false,
                'error' => $mysqli->connect_error,
            );
        }

        $res = $mysqli->query($sql);
        if (!$res) {
            return false;
        }
        $res->data_seek(0);

        if (!$multiple) {
            $row = $res->fetch_assoc();
            return array(
                'success' => true,
                'data' => $row,
            );
        } else {
            $rows = array();
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
            return array(
                'success' => true,
                'data' => $rows,
            );
        }
    }

    function request($params)
    {
        // Authentication
        $username = $this->credentials['ak_user'];
        $password = $this->credentials['ak_pass'];
        $options = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode("$username:$password"),
            ),
            'user-agent' => 'VictoryKit',
        );

        $url = $this->paths['domain'] . $this->paths['rest'] . $this->clean_path($params['path']);
        $data = $params['data'];
        if ($params['method'] == 'get') {
            $url .= '?' . http_build_query($data);
            $response = wp_remote_get($url, $options);
        } else {
            $options['headers']['Content-Type'] = 'application/json';
            $options['body'] = json_encode($data);
            $options['method'] = strtoupper($params['method']);
            $response = wp_remote_post(
                $url,
                $options
            );
        }

        $response['error'] = false;

        if ($response === FALSE) {
            // TODO: Handle errors from AK
        }

        $code = $response['response']['code'];
        if ($code >= 400) {
            $response['error'] = true;
            error_log("Error from ActionKit request to $url with data = " . json_encode($data) . ":\n " . $response['body']);
/*
            mail(
                'tibet+vkalerts@terran.io',
                'VK Alert',
                json_encode(array(
                    'url' => $url,
                    'response' => $response['response'],
                )),
                'From: VK Alerts <alerts@watchdog.net>'
            );
*/
        }

        return $response;
    }

    function get_resource_id($response) {
        if (isset($response['headers']['location'])) {
            $location =  $response['headers']->getAll()['location'];
            preg_match('%/(\d+)/$%', $location, $matches);
            return $matches[1];
        }
        return false;
    }
}

function ak() {
    global $ak;

    if(!isset($ak)) {
        $ak = new ActionKit();
    }

    return $ak;
}

// Initialize
ak();
