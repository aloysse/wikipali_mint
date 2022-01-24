<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaliText;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpgradePaliText extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:palitext {from?} {to?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
		$this->info("upgrade pali text");
		$startTime = time();

		$_from = $this->argument('from');
		$_to = $this->argument('to');
		if(empty($_from) && empty($_to)){
			$_from = 1;
			$_to = 217;
		}else if(empty($_to)){
			$_to = $_from;
		}
#载入文件列表
		$fileListFileName = config("app.path.palitext_filelist");

		$filelist = array();

		if (($handle = fopen($fileListFileName, 'r')) !== false) {
			while (($filelist[] = fgetcsv($handle, 0, ',')) !== false) {
			}
		}

		$bar = $this->output->createProgressBar($_to-$_from+1);

		for ($from=$_from; $from <= $_to; $from++) {
			$inputRow = 0;
			
			$arrInserString = array();
			#载入csv数据
			$FileName = $filelist[$from-1][1];
			$csvFile = config("app.path.palicsv") .'/'. $FileName .'/'. $FileName.'_pali.csv';
			if (($fp = fopen($csvFile, "r")) !== false) {
				while (($data = fgetcsv($fp, 0, ',')) !== false) {
					if ($inputRow > 0) {
						array_push($arrInserString, $data);
					}
					$inputRow++;
				}
				fclose($fp);
				Log::info("csv load：" . $csvFile);
			} else {
				$this->error( "can not open csv file. filename=" . $csvFile. PHP_EOL) ;
				Log::error( "can not open csv file. filename=" . $csvFile) ;
				continue;
			}
			$title_data = PaliText::where('book',$from)->orderby('paragraph','asc')->get();
			DB::transaction(function ()use($from,$arrInserString,$title_data) {
				$paragraph_count = count($title_data);
				$paragraph_info = array();
				$paragraph_info[] = array($from, -1, $paragraph_count, -1, -1, -1);


				for ($iPar = 0; $iPar < count($title_data); $iPar++) {
					$title_data[$iPar]["level"] = $arrInserString[$iPar][3];
				}


				for ($iPar = 0; $iPar < count($title_data); $iPar++) {
					$book = $from ;
					$paragraph = $title_data[$iPar]["paragraph"];

					if ((int) $title_data[$iPar]["level"] == 8) {
						$title_data[$iPar]["level"] = 100;
					}

					$curr_level = (int) $title_data[$iPar]["level"];
					# 计算这个chapter的段落数量
					$length = -1;
				
					
					for ($iPar1 = $iPar + 1; $iPar1 < count($title_data); $iPar1++) {
						$thislevel = (int) $title_data[$iPar1]["level"];
						if ($thislevel <= $curr_level) {
							$length = (int) $title_data[$iPar1]["paragraph"] - $paragraph;
							break;
						}
					}

					if ($length == -1) {
						$length = $paragraph_count - $paragraph + 1;
					}


					$prev = -1;
					if ($iPar > 0) {
						for ($iPar1 = $iPar - 1; $iPar1 >= 0; $iPar1--) {
							if ($title_data[$iPar1]["level"] == $curr_level) {
								$prev = $title_data[$iPar1]["paragraph"];
								break;
							}
						}
					}

					$next = -1;
					if ($iPar < count($title_data) - 1) {
						for ($iPar1 = $iPar + 1; $iPar1 < count($title_data); $iPar1++) {
							if ($title_data[$iPar1]["level"] == $curr_level) {
								$next = $title_data[$iPar1]["paragraph"];
								break;
							}
						}
					}

					$parent = -1;
					if ($iPar > 0) {
						for ($iPar1 = $iPar - 1; $iPar1 >= 0; $iPar1--) {
							if ($title_data[$iPar1]["level"] < $curr_level) {
								$parent = $title_data[$iPar1]["paragraph"];
								break;
							}
						}
					}
					//计算章节包含总字符数
					$iChapter_strlen = 0;

					for ($i = $iPar; $i < $iPar + $length; $i++) {
						$iChapter_strlen += $title_data[$i]["lenght"];
					}
					
					$newData = [
						'level' => $arrInserString[$iPar][3],
						'toc' => $arrInserString[$iPar][5],
						'chapter_len' => $length,
						'next_chapter' => $next,
						'prev_chapter' => $prev,
						'parent' => $parent,
						'chapter_strlen'=> $iChapter_strlen,
					];
					PaliText::where('book',$book)
							->where('paragraph',$paragraph)
							->update($newData);

					if ($curr_level > 0 && $curr_level < 8) {
						$paragraph_info[] = array($book, $paragraph, $length, $prev, $next, $parent);
					}
				}
			});
			$bar->advance();
		}
		$bar->finish();
	
		$this->info("instert pali text finished. in ". time()-$startTime . "s" .PHP_EOL);
		Log::info("instert pali text finished. in ". time()-$startTime . "s");
        return 0;
    }
}