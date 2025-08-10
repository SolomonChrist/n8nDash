<?php
namespace Models;
use Utils\DB;
use PDO;
abstract class BaseModel {
  protected $pdo;
  public function __construct(){ $this->pdo = DB::pdo(); }
}
