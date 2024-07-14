<?php
include("config.inc.php");

class db
{
  function __construct()
  {
    global $db_host;
    global $db_user;
    global $db_pass;
    global $db_name;

    $this->conn = mysqli_connect($db_host,$db_user,$db_pass,$db_name);
    if (!$this->conn)
      {
	echo "Issue connecting to DB on host $db_host.\n";
	return 0;
      }

    $this->conn->query("set names 'utf8'");
  }

  function query ($query)
  {
    $result = $this->conn->query($query);
    if (!$result)
      {
	echo "Syntax problem in '$query'\n";
	return 0;
      }

    return $result;
  }

  function insertid ()
  {
    return $this->conn->insert_id;
  }

  /**
   * Converts the argument of an SQL request in a format accepted by MySQL.
   *
   * @param[in] value String or integer to use as argument
   *
   * @return The string to use in the request
   */
  function quote_smart($value)
  {
    if (!is_numeric($value))
      $value = "'" . $this->conn->real_escape_string($value) . "'";

    return $value;
  }


  // Test whereas the database supports a given feature
  function has_feature($feature)
  {
    // Return -1 on v1 < v2, 0 on v1 = v2 and 1 on v1 > v2
    $compare_versions = function($v1, $v2) {
      for ($i = 0; $i < min(sizeof($v1), sizeof($v2)); $i++)
        if ($v1[$i] != $v2[$i])
          return $v1[$i] - $v2[$i];
      return 0;
    };

    switch ($feature) {
      case 'intersect': // intersect was introduced in mariadb version 10.3.10
        $res = $this->query("select version() version;");
        $ver = mysqli_fetch_object($res)->version;
        preg_match("/^(\d+(?:\.\d+)*)-.+$/", $ver, $match);
        $version = array_map(function ($v) { return (int)$v; }, explode('.', $match[1]));
        return $compare_versions($version, array(10, 3, 10)) >= 0;

      default:
        throw new Exception("Unknown feature", 1);
    }
  }
}
?>
