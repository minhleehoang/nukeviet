<?php

/**
 * @Project NUKEVIET 3.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2012 VINADES.,JSC. All rights reserved
 * @Createdate 2-9-2010 14:43
 */

if( ! defined( 'NV_IS_FILE_THEMES' ) ) die( 'Stop!!!' );

$bid = $nv_Request->get_int( 'bid', 'post' );

list( $bid, $theme, $position ) = $db->query( 'SELECT `bid`, `theme`, `position` FROM `' . NV_BLOCKS_TABLE . '_groups` WHERE `bid`=' . $bid )->fetch( 3 );

if( intval( $bid ) > 0 )
{
	$db->exec( 'DELETE FROM `' . NV_BLOCKS_TABLE . '_groups` WHERE `bid`=' . $bid );
	$db->exec( 'DELETE FROM `' . NV_BLOCKS_TABLE . '_weight` WHERE `bid`=' . $bid );

	// reupdate
	$weight = 0;
	$sth = $db->prepare( 'SELECT bid FROM `' . NV_BLOCKS_TABLE . '_groups` WHERE theme=:theme AND position=:position ORDER BY weight ASC' );
	$sth->bindParam( ':theme', $theme, PDO::PARAM_STR );
	$sth->bindParam( ':position', $position, PDO::PARAM_STR );
	$sth->execute();
	while( list( $bid_i ) = $result->fetch( 3 ) )
	{
		++$weight;
		$db->exec( 'UPDATE `' . NV_BLOCKS_TABLE . '_groups` SET `weight`=' . $weight . ' WHERE `bid`=' . $bid_i );
	}

	$func_id_old = $weight = 0;
	$sth = $db->prepare( 'SELECT t1.bid, t1.func_id FROM `' . NV_BLOCKS_TABLE . '_weight` AS t1 INNER JOIN `' . NV_BLOCKS_TABLE . '_groups` AS t2
		ON t1.bid = t2.bid
		WHERE t2.theme=:theme AND t2.position=:position ORDER BY t1.func_id ASC, t1.weight ASC' );
	$sth->bindParam( ':theme', $theme, PDO::PARAM_STR );
	$sth->bindParam( ':position', $position, PDO::PARAM_STR );
	$sth->execute();
	while( list( $bid_i, $func_id_i ) = $sth->fetch( 3 ) )
	{
		if( $func_id_i == $func_id_old )
		{
			++$weight;
		}
		else
		{
			$weight = 1;
			$func_id_old = $func_id_i;
		}

		$db->exec( 'UPDATE `' . NV_BLOCKS_TABLE . '_weight` SET `weight`=' . $weight . ' WHERE `bid`=' . $bid_i . ' AND `func_id`=' . $func_id_i );
	}

	nv_del_moduleCache( 'themes' );

	$db->exec( 'OPTIMIZE TABLE `' . NV_BLOCKS_TABLE . '_groups`' );
	$db->exec( 'OPTIMIZE TABLE `' . NV_BLOCKS_TABLE . '_weight`' );

	echo $lang_module['block_delete_success'];
}
else
{
	echo $lang_module['block_front_delete_error'];
}

?>