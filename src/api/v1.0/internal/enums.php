<?php
abstract class Privilege
{
    const User = 1;
    const Moderator = 2;
    const Admin = 3;

    public static function ToString($val){
        switch($val)
        {
            case 0:
                return "Unset";
            case Privilege::User:
                return "User";
            case Privilege::Moderator:
                return "Moderator";
            case Privilege::Admin:
                return "Admin";
        }
    }
}
?>