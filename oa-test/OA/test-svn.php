<?php
    svn_auth_set_parameter(SVN_AUTH_PARAM_DEFAULT_USERNAME, 'qingwen.ye');
    svn_auth_set_parameter(SVN_AUTH_PARAM_DEFAULT_PASSWORD, 'hhy');
    svn_update(dirname(__FILE__) . '/calc');
    // svn_checkout('svn://192.168.200.33/', dirname(__FILE__) . '/it');
    // svn_checkout('https://192.168.0.253/svn/dhcp/', dirname(__FILE__) . '/it');
?>