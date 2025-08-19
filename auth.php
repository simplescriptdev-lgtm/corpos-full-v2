<?php
session_start();
function is_authenticated():bool{ return isset($_SESSION['user']); }
function current_user(){ return $_SESSION['user'] ?? null; }
function login_user(array $u){ $_SESSION['user']=['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']]; }
function do_logout(){ session_unset(); session_destroy(); }
