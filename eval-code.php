<?PHP class A{
   public $val = 100;
}

$a = new A();
$a->val = 100000;

//return var_export($a, true);
return A::__set_state(array(
   'val' => 100000,
)) == a 
;