<?php
/* Copyright (C) 2005      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2012 Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin		<regis.houssin@capnetworks.com>
 * Copyright (C) 2014-2015 Marcos García        <marcosgdf@gmail.com>
 * Copyright (C) 2018      Frédéric France      <frederic.france@netlogic.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 *	\file       htdocs/core/modules/processrules/doc/pdf_saumon.modules.php
 *	\ingroup    processrules
 *	\brief      Fichier de la classe permettant de generer les bordereaux envoi au modele saumon
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commondocgenerator.class.php';
require_once __DIR__.'/../../../../class/processrules.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';


/**
 *	Classe permettant de generer les borderaux envoi au modele saumon
 */
class pdf_processrules extends CommonDocGenerator
{
	var $emetteur;	// Objet societe qui emet


	/**
	 *	Constructor
	 *
	 *	@param	DoliDB	$db		Database handler
	 */
	function __construct($db=0)
	{
		global $conf,$langs,$mysoc;

		$this->db = $db;
		$this->name = "processrules";
		$this->description = $langs->trans("DocumentModelStandardPDF");

		$this->type = 'pdf';
		$formatarray=pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array($this->page_largeur,$this->page_hauteur);
		$this->marge_gauche=isset($conf->global->MAIN_PDF_MARGIN_LEFT)?$conf->global->MAIN_PDF_MARGIN_LEFT:10;
		$this->marge_droite=isset($conf->global->MAIN_PDF_MARGIN_RIGHT)?$conf->global->MAIN_PDF_MARGIN_RIGHT:10;
		$this->marge_haute =isset($conf->global->MAIN_PDF_MARGIN_TOP)?$conf->global->MAIN_PDF_MARGIN_TOP:10;
		$this->marge_basse =isset($conf->global->MAIN_PDF_MARGIN_BOTTOM)?$conf->global->MAIN_PDF_MARGIN_BOTTOM:10;

		$this->option_logo = 1;

		// Get source company
		$this->emetteur=$mysoc;
		if (! $this->emetteur->country_code) $this->emetteur->country_code=substr($langs->defaultlang,-2);    // By default if not defined


		$this->tabTitleHeight = 5; // default height

	}

	/**
	 *	Function to build pdf onto disk
	 *
	 *	@param		Object		$object			Object processrules to generate (or id if old method)
	 *	@param		Translate	$outputlangs		Lang output object
	 *  @param		string		$srctemplatepath	Full path of source filename for generator using a template file
	 *  @param		int			$hidedetails		Do not show line details
	 *  @param		int			$hidedesc			Do not show desc
	 *  @param		int			$hideref			Do not show ref
	 *  @return     int         	    			1=OK, 0=KO
	 */
	function write_file($object,$outputlangs,$srctemplatepath='',$hidedetails=0,$hidedesc=0,$hideref=0)
	{
		global $user,$conf,$langs,$hookmanager;

		$object->fetch_thirdparty();

		if (! is_object($outputlangs)) $outputlangs=$langs;
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (! empty($conf->global->MAIN_USE_FPDF)) $outputlangs->charset_output='ISO-8859-1';

		// Translations
		$outputlangs->loadLangs(array("main", "bills", "products", "dict", "companies", "propal", "deliveries", "sendings", "productbatch"));

		$nblignes = 0 ;
		if(!empty($object->lines)){
			$nblignes = count($object->lines);
		}




		if ($conf->processrules->dir_output)
		{
			// Definition de $dir et $file
			if ($object->specimen)
			{
				$dir = $conf->processrules->dir_output."/sending";
				$file = $dir . "/SPECIMEN.pdf";
			}
			else
			{
				$expref = dol_sanitizeFileName($object->ref);
				$dir = $conf->processrules->dir_output."/" . $expref;
				$file = $dir . "/" . $expref . ".pdf";
			}

			if (! file_exists($dir))
			{
				if (dol_mkdir($dir) < 0)
				{
					$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
					return 0;
				}
			}

			if (file_exists($dir))
			{
				// Add pdfgeneration hook
				if (! is_object($hookmanager))
				{
					include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
					$hookmanager=new HookManager($this->db);
				}
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('beforePDFCreation',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks

				// Set nblignes with the new object lines content after hook
				$nblignes = 0 ;
				if(!empty($object->lines)){
					$nblignes = count($object->lines);
				}

				$pdf=pdf_getInstance($this->format);
				$this->default_font_size = pdf_getPDFFontSize($outputlangs);
				$heightforinfotot = 8;	// Height reserved to output the info and total part
				$heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:5);	// Height reserved to output the free text on last page
				$heightforfooter = $this->marge_basse + 8;	// Height reserved to output the footer (value include bottom margin)
				if ($conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS >0) $heightforfooter+= 6;
				$pdf->SetAutoPageBreak(1,0);

				if (class_exists('TCPDF'))
				{
					$pdf->setPrintHeader(false);
					$pdf->setPrintFooter(false);
				}
				$pdf->SetFont(pdf_getPDFFont($outputlangs));
				// Set path to the background PDF File
				if (! empty($conf->global->MAIN_ADD_PDF_BACKGROUND))
				{
					$pagecount = $pdf->setSourceFile($conf->mycompany->dir_output.'/'.$conf->global->MAIN_ADD_PDF_BACKGROUND);
					$tplidx = $pdf->importPage(1);
				}

				$pdf->Open();
				$pagenb=0;
				$pdf->SetDrawColor(128,128,128);

				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();

				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("Shipment"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("Shipment"));
				if (! empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) $pdf->SetCompression(false);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right

				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$this->_pagehead($pdf, $object, 1, $outputlangs);
				$this->resetDefaultFont($pdf);
				$pdf->MultiCell(0, 3, '');		// Set interline to 3

				$this->_pagefoot($pdf,$object,$outputlangs);

				$curentY = 40;
				$tab_top_newpage = (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)?42:10);


				$displayNoteParam = array('object' => $object, 'y' => $curentY);
				$displayNoteMethod = array($this, 'displayNote');
				$curentY = $this->tryToPrint($pdf, $displayNoteMethod, true, $displayNoteParam);

				$object->fetch_lines();

				// Loop on each lines
				if(!empty($object->lines)){
					foreach ($object->lines as $procedure)
					{
						$displayParam = array(
							'object' => $object,
							'y' => $curentY,
							'procedure' => $procedure
						);
						$displayMethod = array($this, 'displayProcedure');
						$curentY = $this->tryToPrint($pdf, $displayMethod, true, $displayParam);
						$curentY + 6;
					}
				}





				// Pied de page
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();

				$pdf->Close();

				$pdf->Output($file,'F');

				// Add pdfgeneration hook
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('afterPDFCreation',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks

				if (! empty($conf->global->MAIN_UMASK))
					@chmod($file, octdec($conf->global->MAIN_UMASK));

				$this->result = array('fullpath'=>$file);

				return 1;	// No error
			}
			else
			{
				$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}
		else
		{
			$this->error=$langs->transnoentities("ErrorConstantNotDefined","EXP_OUTPUTDIR");
			return 0;
		}
	}


	/**
	 *  Show top header of page.
	 *
	 *  @param	PDF			$pdf     		Object PDF
	 *  @param  Object		$object     	Object to show
	 *  @param  int	    	$showaddress    0=no, 1=yes
	 *  @param  Translate	$outputlangs	Object lang for output
	 *  @return	void
	 */
	function _pagehead(&$pdf, $object, $showaddress, $outputlangs)
	{
		global $conf,$langs,$mysoc;

		$langs->load("orders");

		$this->default_font_size = pdf_getPDFFontSize($outputlangs);

		pdf_pagehead($pdf,$outputlangs,$this->page_hauteur);

		// Show Draft Watermark
		if($object->statut==0 && (! empty($conf->global->SHIPPING_DRAFT_WATERMARK)) )
		{
			pdf_watermark($pdf,$outputlangs,$this->page_hauteur,$this->page_largeur,'mm',$conf->global->SHIPPING_DRAFT_WATERMARK);
		}

		//Prepare la suite
		$pdf->SetTextColor(0,0,60);
		$pdf->SetFont('','B', $this->default_font_size + 3);

		$w = 110;

		$posy=$this->marge_haute;
		$posx=$this->page_largeur-$this->marge_droite-$w;

		$pdf->SetXY($this->marge_gauche,$posy);

		// Logo
		$logo=$conf->mycompany->dir_output.'/logos/'.$this->emetteur->logo;
		if ($this->emetteur->logo)
		{
			if (is_readable($logo))
			{
				$height=pdf_getHeightForLogo($logo);
				$pdf->Image($logo, $this->marge_gauche, $posy, 0, $height);	// width=0 (auto)
			}
			else
			{
				$pdf->SetTextColor(200,0,0);
				$pdf->SetFont('','B', $this->default_font_size - 2);
				$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound",$logo), 0, 'L');
				$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
			}
		}
		else
		{
			$text=$this->emetteur->name;
			$pdf->MultiCell($w, 4, $outputlangs->convToOutputCharset($text), 0, 'L');
		}

		// Show barcode
		if (! empty($conf->barcode->enabled))
		{
			$posx=105;
		}
		else
		{
			$posx=$this->marge_gauche+3;
		}



		$posx=$this->page_largeur - $w - $this->marge_droite;
		$posy=$this->marge_haute;

		$pdf->SetFont('','B', $this->default_font_size + 2);
		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColor(0,0,60);
		$title=$outputlangs->transnoentities("PDFProcessruleTitle");
		$pdf->MultiCell($w, 4, $title, '', 'R');

		$pdf->SetFont('','', $this->default_font_size + 1);

		$posy+=5;

		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColor(0,0,60);
		$pdf->MultiCell($w, 4, $outputlangs->transnoentities("PDFProcessruleRef") ." : ".$object->ref, '', 'R');


		// reset to default font color ans size
		$pdf->SetFont('','', $this->default_font_size + 3);
		$pdf->SetTextColor(0,0,0);
	}

	/**
	 *   	Show footer of page. Need this->emetteur object
	 *
	 *   	@param	PDF			$pdf     			PDF
	 * 		@param	Object		$object				Object to show
	 *      @param	Translate	$outputlangs		Object lang for output
	 *      @param	int			$hidefreetext		1=Hide free text
	 *      @return	int								Return height of bottom margin including footer text
	 */
	function _pagefoot(&$pdf,$object,$outputlangs,$hidefreetext=0)
	{
		global $conf;
		$showdetails=$conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS;
		return pdf_pagefoot($pdf,$outputlangs,'SHIPPING_FREE_TEXT',$this->emetteur,$this->marge_basse,$this->marge_gauche,$this->page_hauteur,$object,$showdetails,$hidefreetext);
	}

	/**
	 * @param TCPDF $pdf
	 * @param string $method
	 * @param bool $autoPageBreak
	 * @param array $param
	 * @return float Y position
	 */
	public function tryToPrint(&$pdf, $callback, $autoPageBreak = true, $param = array())
	{
		global $conf, $outputlangs;

		if (is_callable($callback))
		{


			$pdf->startTransaction();
			$posYBefore = $pdf->GetY();
			$pageposBefore=$pdf->getPage();

			// START FIRST TRY
			$res = call_user_func_array($callback, array(&$pdf, $param));

			$pageposAfter=$pdf->getPage();
			$posYAfter = $pdf->GetY();

			// END FIRST TRY



			//if ($method == 'printNotes') {var_dump('yes',$pageposafter>$pageposbefore, $pageposafter, $pageposbefore,$posybefore, $posyafter); exit;}
			if($autoPageBreak && $pageposAfter > $pageposBefore )
			{
				$pagenb = $pageposBefore;
				$pdf->rollbackTransaction(true);
				$posY = $posYBefore;

				// prepare pages to receive content
				while ($pagenb < $pageposAfter) {
					$pdf->AddPage();
					$pagenb++;

					if (! empty($tplidx)) $pdf->useTemplate($tplidx);

					if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $this->object, 0, $outputlangs);

					$topY = $pdf->GetY() + 20;
					$pdf->SetMargins($this->marge_gauche, $topY, $this->marge_droite); // Left, Top, Right

					$pdf->SetAutoPageBreak(0, 0); // to prevent footer creating page
					$footerheight = $this->_pagefoot($pdf,$this->object, $outputlangs);
					$pdf->SetAutoPageBreak(1, $footerheight);

					// The only function to edit the bottom margin of current page to set it.
					$pdf->setPageOrientation('', 1, $footerheight);
				}

				// BACK TO START
				$pdf->setPage($pageposBefore);
				$pdf->SetY($posYBefore);

				// RESTART DISPLAY BLOCK - without auto page break
				$posY = $this->tryToPrint($pdf, $callback, false, $param);

			}
			else // No pagebreak
			{
				$pdf->commitTransaction();
			}

			return $pdf->GetY();
		}
	}

	/**
	 * @param TCPDF $pdf
	 * @param array $param
	 * @return float Y position
	 */
	public function displayNote($pdf, $param){
		$y = $param['y'];
		$object = $param['object'];

		if (! empty($object->note_public))
		{
			// Notes
			if (! empty($object->note_public))
			{
				$pdf->SetFont('','', $this->default_font_size - 1);   // Dans boucle pour gerer multi-page
				$pdf->writeHTMLCell(190, 3, $this->marge_gauche+1, $y, dol_htmlentitiesbr($object->note_public), 0, 1);
			}

			$nexY = $pdf->GetY();
			$height_note=$nexY-$y;

			// Rect prend une longueur en 3eme param
			$pdf->SetDrawColor(192,192,192);
			$pdf->Rect($this->marge_gauche, $y-1, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $height_note+1);

			$y = $nexY+6;
			$pdf->SetY($y);
		}

		return $y;
	}


	function displayProcedure($pdf, $param){
		global $langs;


		//($procedure, $htmlId='', $open = true, $editable = true)

		$y = $param['y'];
		$object = $param['object'];
		$procedure = $param['procedure'];

		$fullWidth = $this->page_largeur-$this->marge_gauche-$this->marge_droite;

		/**
		 * @var $procedure Procedure
		 * @var $pdf TCPDF
		 */

		$curY = $y;
		// Titre de la procedure
		$pdf->SetXY($this->marge_gauche, $curY+1);
		$pdf->SetFont('','', $this->default_font_size + 5);
		$pdf->MultiCell($fullWidth, 3, $procedure->getNom() , 1, 'L');

		// Description
		$curY = $pdf->getY() + 2;

		if(!empty($procedure->description))
		{
			$this->resetDefaultFont($pdf);
			$pdf->writeHTMLCell($fullWidth, 3, $this->marge_gauche, $curY, dol_htmlentitiesbr($procedure->description), 0, 1);

			$curY = $pdf->getY();
			$pdf->setY($curY + 6);
		}

	}

	public function resetDefaultFont($pdf){
		$pdf->SetFont('','', $this->default_font_size - 1);
		$pdf->SetTextColor(0,0,0);
	}
}

