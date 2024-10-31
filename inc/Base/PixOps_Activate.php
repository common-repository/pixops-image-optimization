<?php
namespace Inc\Base;

class PixOps_Activate
{
	public static function pixops_activate() {
            flush_rewrite_rules();
	}
}