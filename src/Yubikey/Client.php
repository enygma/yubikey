<?php
//http://www.onlineaspect.com/2009/01/26/how-to-use-curl_multi-without-blocking/

namespace Yubikey;

class Client
{
    /**
     * Send the request(s) via curl
     *
     * @param  array|\Yukikey\RequestCollection $requests Set of \Yubikey\Request objects
     * @return \Yubikey\ResponseCollection instance
     */
    public function send($requests)
    {
        if (get_class($requests) !== 'Yubikey\\RequestCollection') {
            $requests = new \Yubikey\RequestCollection($requests);
        }

        $multi = curl_multi_init();
        $curls = array();
        $content = array(
            'response' => null,
            'host' => null
        );
        $mt = microtime(true);

        // this should be a collection too
        $responses = new \Yubikey\ResponseCollection();

        foreach ($requests as $index => $request) {
            $curls[$index] = curl_init();
            curl_setopt_array($curls[$index], array(
                CURLOPT_URL => $request->getUrl(),
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => 1
            ));
            curl_multi_add_handle($multi, $curls[$index]);
        }

        do {
            while (($mrc = curl_multi_exec($multi, $active)) == CURLM_CALL_MULTI_PERFORM);
            while ($info = curl_multi_info_read($multi)) {
                if ($info['result'] == CURLE_OK) {
                    $return = curl_multi_getcontent($info['handle']);
                    $cinfo = curl_getinfo($info['handle']);
                    $url = parse_url($cinfo['url']);

                    $response = new \Yubikey\Response(array(
                        'host' => $url['host'],
                        'mt' => (microtime(true)-$mt)
                    ));
                    $response->parse($return);
                    $responses->add($response);
                }
            }
        } while ($active);

        return $responses;
    }
}