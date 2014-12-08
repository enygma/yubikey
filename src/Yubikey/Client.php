<?php
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

        $responses = $this->request($requests);
        return $responses;
    }

    /**
     * Make the request given the Request set and content
     *
     * @param \Yubikey\RequestCollection $requests Request collection
     * @return \Yubikey\ResponseCollection instance
     */
    public function request(\Yubikey\RequestCollection $requests)
    {
        $responses = new \Yubikey\ResponseCollection();
        $startTime = microtime(true);
        $multi = curl_multi_init();
        $curls = array();

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
            while ((curl_multi_exec($multi, $active)) == CURLM_CALL_MULTI_PERFORM);
            while ($info = curl_multi_info_read($multi)) {
                if ($info['result'] == CURLE_OK) {
                    $return = curl_multi_getcontent($info['handle']);
                    $cinfo = curl_getinfo($info['handle']);
                    $url = parse_url($cinfo['url']);

                    $response = new \Yubikey\Response(array(
                        'host' => $url['host'],
                        'mt' => (microtime(true)-$startTime)
                    ));
                    $response->parse($return);
                    $responses->add($response);
                }
            }
        } while ($active);

        return $responses;
    }
}