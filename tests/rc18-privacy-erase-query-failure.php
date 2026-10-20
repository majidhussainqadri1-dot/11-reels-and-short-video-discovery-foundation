<?php
/** RC18: privacy erasure may not report completion on a failed ownership count.
 * Transaction mock asserts that SQL uncertainty triggers rollback, not COMMIT. */
require __DIR__.'/bootstrap.php';
function get_user_by($by,$email) { return $email==='found@example.test'?(object)array('ID'=>7):false; }
function apply_filters($tag,$default,...$args){return $default;}
final class RC18_Erase_DB {
    public $prefix='wp_'; public $last_error=''; public $next_value=null;
    public $calls=array();
    public function prepare($sql,...$params){return $sql;}
    public function delete($table,$where,$formats=array()){ $this->calls[]='DELETE';return 0; }
    public function update($table,$data,$where,$data_formats=array(),$where_formats=array()){ $this->calls[]='UPDATE';return 0; }
    public function query($sql){ $this->calls[]=$sql;return 1; }
    public function get_var($sql){$this->calls[]='GET_VAR';return $this->next_value;}
}
$GLOBALS['wpdb']=new RC18_Erase_DB();
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-helpers.php';
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-db.php';
require dirname(__DIR__).'/11-reels-foundation/includes/class-rsv-privacy.php';
$privacy=new RSV_Privacy();$n=0;$errors=array();
foreach(array(
 'database null'=>array(null,''),
 'database false'=>array(false,''),
 'database last_error'=>array('0','SQL server gone'),
 'malformed nonnumeric'=>array('invalid',''),
 'empty string count'=>array('','')
) as $name=>$case) {
    ++$n;
    $GLOBALS['wpdb']->calls=array();
    $GLOBALS['wpdb']->next_value=$case[0];
    $GLOBALS['wpdb']->last_error=$case[1];
    $result=$privacy->erase('found@example.test',1);
    if(($result['done']??true)!==false || ($result['items_removed']??true)!==false || !in_array('ROLLBACK',$GLOBALS['wpdb']->calls,true) || in_array('COMMIT',$GLOBALS['wpdb']->calls,true))
        $errors[]="$name must fail/rollback without certified erasure";
}
if($n!==5)$errors[]='fixture count';
if($errors){fwrite(STDERR,"RC18 erasure failure:\n".implode("\n",$errors)."\n");exit(1);}
echo "RC18 privacy erasure 5/5 fail-closed retained-ownership SQL/rollback scenarios PASS\n";
