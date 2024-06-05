<?php
namespace booosta\hetzner_com;

use \booosta\Framework as b;
b::init_module('hetzner_com');


class Hetzner_com extends \booosta\base\Module
{
  use moduletrait_hetzner_com;

  protected $restapp;


  public function __construct(protected $token = null, protected $url = null)
  {
    parent::__construct();

    if($token === null) $this->token = $this->config('hetzner_com-token');
    if($url === null) $this->url = 'https://dns.hetzner.com/api/v1/';

    $this->restapp = $this->makeInstance('restapp', $this->url);
  }

  public function __call($name, $args)
  {
    return $this->restapp(...$args);
  }
}


class restapp extends \booosta\rest\Application
{
  public function __construct($url)
  {
    parent::__construct();
    $this->url = $url;
  }

  public function get_zones()
  {
    return $this->get('/zones');
  }
}

