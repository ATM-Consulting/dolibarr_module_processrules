<?php
/* Copyright (C) 2004-2017	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2006		Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2007-2017	Regis Houssin			<regis.houssin@capnetworks.com>
 * Copyright (C) 2011		Philippe Grand			<philippe.grand@atoo-net.com>
 * Copyright (C) 2012		Juanjo Menent			<jmenent@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FI8TNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *		\file       htdocs/theme/eldy/style.css.php
 *		\brief      File for CSS style sheet Eldy
 */



define('INC_FROM_CRON_SCRIPT',1);


session_cache_limiter(FALSE);

require_once __DIR__ . '/../config.default.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';


// Load user to have $user->conf loaded (not done into main because of NOLOGIN constant defined)
if (empty($user->id) && ! empty($_SESSION['dol_login'])) $user->fetch('',$_SESSION['dol_login'],'',1);


// Define css type
header("Content-Type: text/css");
// Important: Following code is to avoid page request by browser and PHP CPU at each Dolibarr page access.
if (empty($dolibarr_nocache)) header('Cache-Control: max-age=3600, public, must-revalidate');
else header('Cache-Control: no-cache');

$imgWidth = (!empty($conf->global->PROCESSRULES_STEPIMG_MAXWIDTH) ? $conf->global->PROCESSRULES_STEPIMG_MAXWIDTH : '300px');

?>

ul.pr-sortable-list,ul.pr-sortable-list ul,ul.pr-sortable-list li, #sortableListsBase ul, #sortableListsBase li {
	margin:0; padding:0;
	list-style-type:none;
	color:#6e6e6e;
	border:1px solid #c3c3c3;
}

ul.pr-sortable-list{ padding:0; background-color:#f9f9f9; }

ul.pr-sortable-list li.pr-sortable-list__item, #sortableListsBase li.pr-sortable-list__item{
	padding-left:50px;
	margin:5px;
	border: 1px solid #c3c3c3;
	background-color: #dcdcdc;
}

ul.pr-sortable-list.steps li.pr-sortable-list__item {background-color: #a0a0a0;}

li.pr-sortable-list__item .pr-sortable-list__item__title , #sortableListsBase li.pr-sortable-list__item .pr-sortable-list__item__title {
	padding:7px;
	background-color:#fff;
}

.move {
	cursor: move;
}

.clickable, .move .clickable{
	cursor: pointer;
}

.sortableListsOpener{
	cursor: pointer !important;
}

.pr-sortable-list__item--placeholder{
	background-color: #ff8 !important;
}

.pr-sortable-list__item--hint{
	background-color: #bbf !important;
}

.pr-sortable-list__item__title__flex{
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
}

.pr-sortable-list__item__title__col{
	flex: auto;
	width: 25%;
}

.pr-sortable-list__item__title__col.-action{
	text-align: right;
}

.pr-sortable-list__item__title__col.-label{
	font-size: 1.2em;
	color: #333;
	font-weight: bold;
}

.pr-sortable-list__item__title__button{
	margin-left: 15px;
}

.pr-sortable-list__item__images{
	#max-height: 120px;
	margin-right: 10px;
	max-width: <?php echo $imgWidth; ?>;
	display: inline-block;
	box-shadow: 1px 1px 3px 0px rgba(0,0,0,0.70);
}

.pr-sortable-list__item__images img{
	max-height: 100%;
	max-width: 100%;
	display: inline-block;
}
