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
    if($url === null) $this->url = 'https://dns.hetzner.com/api/v1';

    $this->restapp = $this->makeInstance("\\booosta\\hetzner_com\\restapp", $this->url, $this->token);
  }

  public function __call($name, $args)
  {
    return $this->restapp->$name(...$args);
  }
}


class restapp extends \booosta\rest\Application
{
  public function __construct($url, $token)
  {
    parent::__construct();

    $this->url = $url;
    $this->headers = ['Auth-API-Token' => $token];
  }

  public function get_zones()
  {
    #b::debug($this->get('/zones'));
    return $this->get('/zones');
  }

  public function get_zone_id($name)
  {
    $zones = $this->get_zones();
    foreach($zones['zones'] as $zone) if($zone['name'] == $name) return $zone['id'];

    return null;
  }

  public function get_zone($id)
  {
    return $this->get("/zones/$id");
  }

  public function get_zone_name($id)
  {
    $zone = $this->get_zone($id);
    return $zone['zone']['name'];
  }

  public function create_zone($name, $ttl = 86400)
  {
    return $this->post('/zones', ['name' => $name, 'ttl' => $ttl]);
  }

  public function update_zone_ttl($id, $ttl)
  {
    return $this->put("/zones/$id", ['name' => $this->get_zone_name($id), 'ttl' => $ttl]);
  }

  public function delete_zone($id)
  {
    return $this->delete("/zones/$id");
  }

  public function create_record($zone_id, array $record)
  {
    $record['zone_id'] = $zone_id;
    return $this->post('/records', $record);
  }

  public function create_records($zone_id, array $records)
  {
    foreach($records as $idx => $record):
      if(!is_array($record)) return false;
      $records[$idx]['zone_id'] = $zone_id;
    endforeach;

    return $this->post('/records/bulk', ['records' => $records]);
  }

  public function get_records($zone_id)
  {
    return $this->get("/records?zone_id=$zone_id");
  }

  public function get_record($id)
  {
    return $this->get("/records/$id");
  }

  public function delete_record($id)
  {
    return $this->delete("/records/$id");
  }
}

