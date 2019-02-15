<?php
namespace App\Http;


use Faker\Provider\DateTime;
use Illuminate\Support\Facades\DB;
use MCSDK\Authentication\FakeDiscoveryOptions;
use MCSDK\Discovery\DiscoveryResponse;
use MCSDK\Utils\RestResponse;


class DatabaseHelper
{
    public function writeDiscoveryResponseToDatabase($state, DiscoveryResponse $discoveryResponse)
    {
        $rawDiscoveryResponse = (new FakeDiscoveryOptions())->fromDiscoveryResponse($discoveryResponse)->getJson();

        DB::connection("mysql")->insert('insert into discovery (id, value) values(?,?)', [$state, $rawDiscoveryResponse]);
    }

    public function getDiscoveryResponseFromDatabase($state){
        $json = DB::connection("mysql")->select('select * from discovery where id=?', [$state])[0]->value;
        DB::connection("mysql")->table('discovery')->where('id', '=', $state)->delete();
        $response = new RestResponse(200, $json);
        $discoveryResponse = new DiscoveryResponse($response);
        return $discoveryResponse;
    }

    public function clearDiscoveryCache($discoveryResponse){
        $rawDiscoveryResponse = (new FakeDiscoveryOptions())->fromDiscoveryResponse($discoveryResponse)->getJson();
        DB::connection("mysql")->table('discovery_cache')->where('value', '=', $rawDiscoveryResponse)->delete();
    }

    public function clearDiscoveryCacheByState($state){
        DB::connection("mysql")->table('discovery')->where('id', '=', $state)->delete();
    }

    public function setCachedDiscoveryResponseByMsisdn($msisdn, $discoveryResponse){
        $date = $discoveryResponse->getTtl()->format("c");
        $rawDiscoveryResponse = (new FakeDiscoveryOptions())->fromDiscoveryResponse($discoveryResponse)->getJson();
        DB::connection("mysql")->insert('insert into discovery_cache (msisdn, mcc, mnc, ip, exp, value) values(?,?,?,?,?,?)',
            [$msisdn, null, null, null, $date, $rawDiscoveryResponse]);
    }

    public function setCachedDiscoveryResponseByMccMnc($mcc, $mnc, $discoveryResponse){
        $date = $discoveryResponse->getTtl()->format("c");
        $rawDiscoveryResponse = (new FakeDiscoveryOptions())->fromDiscoveryResponse($discoveryResponse)->getJson();
        DB::connection("mysql")->insert('insert into discovery_cache (msisdn, mcc, mnc, ip, exp, value) values(?,?,?,?,?,?)',
            [null, $mcc, $mnc, null, $date, $rawDiscoveryResponse]);
    }

    public function setCachedDiscoveryResponseByIp($ip, $discoveryResponse){
        $date = $discoveryResponse->getTtl()->format("c");
        $rawDiscoveryResponse = (new FakeDiscoveryOptions())->fromDiscoveryResponse($discoveryResponse)->getJson();
        DB::connection("mysql")->insert('insert into discovery_cache (msisdn, mcc, mnc, ip, exp, value) values(?,?,?,?,?,?)',
            [null, null, null, $ip, $date, $rawDiscoveryResponse]);
    }


    public function getCachedDiscoveryResponse($msisdn, $mcc, $mnc, $sourceIp){
        $discovery_response = $this->getDiscoveryResponseByMsisdn($msisdn);
        if(empty($discovery_response)){
            $discovery_response = $this->getDiscoveryResponseByMccMnc($mcc, $mnc);
        }
        if(empty($discovery_response)){
            $discovery_response = $this->getDiscoveryResponseByIP($sourceIp);
        }
        if (empty($discovery_response)){
            return null;
        }
        return new DiscoveryResponse(new RestResponse(200, $discovery_response));
    }

    public function writeNonceToDatabase($state, $nonce){
        DB::connection("mysql")->insert('insert into nonce (id, value) values(?,?)', [$state, $nonce]);
    }

    public function getNonceFromDatabase($state){

        $nonce = DB::connection("mysql")->select('select * from nonce where id=?', [$state])[0]->value;
        DB::connection("mysql")->table('nonce')->where('id', '=', $state)->delete();
        return $nonce;
    }


    private function getDiscoveryResponseByMccMnc($mcc, $mnc){
        if(!empty($mcc) && !empty($mnc)){
            $cached =  DB::connection("mysql")->select('select * from discovery_cache where mcc=? AND mnc=?', [$mcc, $mnc])[0];
            $exp = $cached->exp;
            $exp_date = DateTime::createFromFormat("c", $exp);
            if($exp_date>new DateTime()){
                return $cached->value;
            }
            else{
                DB::connection("mysql")->table('discovery_cache')->where('mcc', '=', $mcc)->where('mnc','=',$mnc)->delete();
                return null;
            }
        }
        return null;
    }


    private function getDiscoveryResponseByMsisdn($msisdn) {
        $cached = DB::connection("mysql")->select('select * from discovery_cache where msisdn=?', [$msisdn])[0];
        $exp = $cached->exp;
        $exp_date = DateTime::createFromFormat("c", $exp);
        if ($exp_date > new DateTime()) {
            return $cached->value;
        } else {
            DB::connection("mysql")->table('discovery_cache')->where('msisdn', '=', $msisdn)->delete();
            return null;
        }
    }


    private function getDiscoveryResponseByIP($ip) {
        $cached = DB::connection("mysql")->select('select * from discovery_cache where ip=?', [$ip])[0];
        $exp = $cached->exp;
        $exp_date = DateTime::createFromFormat("c", $exp);
        if ($exp_date > new DateTime()) {
            return $cached->value;
        } else {
            DB::connection("mysql")->table('discovery_cache')->where('ip', '=', $ip)->delete();
            return null;
        }
    }
}