<?php
include(dirname(__FILE__) . "/../web/config.inc.php");
include(dirname(__FILE__) . "/../web/db.inc.php");

$buildresultdir = $maindir . "/results/";

function bab_header($title)
{
  echo "<html>\n";
  echo "<head>\n";
  echo "  <title>$title</title>\n";
  echo "  <link rel=\"stylesheet\" type=\"text/css\" href=\"stylesheet.css\"/>\n";
  echo "  <link rel=\"alternate\" href=\"rss.php\" title=\"Autobuild Buildroot results\" type=\"application/rss+xml\" />\n";
  echo "</head>\n";
  echo "<body>\n";
  echo " <h1>$title</h1>\n";
}

function bab_footer()
{
  echo "<p style=\"width: 90%; margin: auto; text-align: center; font-size: 60%; border-top: 1px solid black; padding-top: 5px;\">\n";
  echo "<a href=\"http://buildroot.org\">About Buildroot</a>&nbsp;-&nbsp;";
  echo "<a href=\"rss.php\">RSS feed of build results</a>&nbsp;-&nbsp;";
  echo "<a href=\"stats.php\">build stats</a>&nbsp;-&nbsp;";
  echo "<a href=\"stats/\">package stats</a>&nbsp;-&nbsp;";
  echo "<a href=\"toolchains/\">toolchain configs</a>&nbsp;-&nbsp;";
  echo "<a href=\"search.php\">advanced search</a>&nbsp;-&nbsp;";
  echo "<a href=\"http://git.buildroot.net/buildroot-test/plain/utils/br-reproduce-build\">Script to reproduce a build</a>\n";
  echo "</p>\n";
  echo "</body>\n";
  echo "</html>\n";
}

function bab_format_sql_config_symbol_filter($db, $symbols)
{
  $get_res_id = "select result_id id from symbol_per_result where symbol_id = (select id from config_symbol where name=%s and value=%s)";

  $r = array_map(
    function($name, $value) use ($db, $get_res_id) {
      return sprintf($get_res_id, $db->quote_smart($name), $db->quote_smart($value));
    },
    array_keys($symbols),
    $symbols
  );

  if ($db->has_feature('intersect'))
    return implode(" intersect ", $r);
  else
    return implode(" and result_id in (", $r) . str_repeat(")", count($symbols)-1);
}

function bab_format_sql_filter($db, $filters)
{
  $status_map = array(
    "OK" => 0,
    "NOK" => 1,
    "TIMEOUT" => 2,
  );

  # Move the symbols away from filters since implode wouldn't work with an empty key
  $symbols = $filters['symbols'];
  unset($filters['symbols']);

  $sql_filters = implode(' and ', array_map(
    function ($v, $k) use ($db, $status_map) {
      if ($k == "reason")
        return sprintf("%s like %s", $k, $db->quote_smart($v));
      else if ($k == "status")
        return sprintf("%s=%s", $k, $db->quote_smart($status_map[$v]));
      elseif ($k == "date")
        if (is_array($v)) {
          if (isset($v['from'], $v['to']))
            return sprintf("builddate between %s and %s", $db->quote_smart($v['from']), $db->quote_smart($v['to']));
          else if (isset($v['to']))
            return sprintf("builddate<=%s", $db->quote_smart($v['to']));
          else
            return sprintf("builddate>=%s", $db->quote_smart($v['from']));
        } else // Assuming the date is a lower-bound
          return sprintf("builddate>=%s", $db->quote_smart($v));
      else
        return sprintf("%s=%s", $k, $db->quote_smart($v));
    },
    $filters,
    array_keys($filters)
  ));

  $sql = "";
  if ($symbols) {
    $symbols_condition = bab_format_sql_config_symbol_filter($db, $symbols);
    $sql .= " inner join ($symbols_condition) symbols using (id)";

  }
  if (count($filters) != 0)
    $sql .= " where $sql_filters";

  return $sql;
}

/*
 * Returns the total number of results.
 */
function bab_total_results_count($filters)
{
  $db = new db();
  $condition = bab_format_sql_filter($db, $filters);
  $sql = "select count(*) from results$condition;";
  $ret = $db->query($sql);
  if ($ret == FALSE) {
    echo "Something's wrong in here\n";
    return;
  }

  $ret = mysqli_fetch_array($ret);
  return $ret[0];
}

/*
 * Returns an array containing the build results starting from $start,
 * and limited to $count items. The items starting with $start=0 are
 * the most recent build results.
 */
function bab_get_results($start=0, $count=100, $filters = array())
{
  global $status_map;
  $db = new db();

  $condition = bab_format_sql_filter($db, $filters);
  $sql = "select * from results$condition order by builddate desc limit $start, $count;";
  $ret = $db->query($sql);
  if ($ret == FALSE) {
    echo "Something's wrong with the SQL query\n";
    return;
  }

  return $ret;
}

function bab_get_path($identifier, $file="") {
  return "results/" . substr($identifier, 0, 3) . "/" . $identifier . "/" . $file;
}

?>
