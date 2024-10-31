<?php
namespace Inc\Base;

class PixOps_Deactivate
{
	public static function pixops_deactivate() {
            flush_rewrite_rules();
	}
}