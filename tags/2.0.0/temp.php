
/*
// Actually do the printing of the calendar
function my_calendar($name,$format,$category,$showkey,$shownav,$showjump,$toggle,$time='month',$ltype='',$lvalue='',$id='jd-calendar',$template='',$content='') {
    global $wpdb, $wp_plugin_url;
	$mcdb = $wpdb;
	if ( get_option( 'mc_remote' ) == 'true' && function_exists('mc_remote_db') ) { $mcdb = mc_remote_db(); }
	$my_calendar_body = '';
	$args = array('name'=>$name,'format'=>$format,'category'=>$category,'showkey'=>$showkey,'shownav'=>$shownav,'toggle'=>$toggle,'time'=>$time,'ltype'=>$ltype,'lvalue'=>$lvalue);
	$my_calendar_body .= apply_filters('mc_before_calendar','',$args);
	$main_class = ( $name !='' )?sanitize_title($name):'all';
	$cid = ( isset( $_GET['cid'] ) )?esc_attr(strip_tags($_GET['cid'])):'all';
	if ( get_option('mc_mobile') == 'true' ) {
		$format = ( mc_is_mobile() )?'list':$format;
	}
	$date_format = ( get_option('mc_date_format') != '' )?get_option('mc_date_format'):get_option('date_format');
	
	if ( $format != 'mini' && $toggle == 'yes' ) {
		$format_toggle = "<div class='mc-format'>";
		$current_url = mc_get_current_url();
		switch ($format) {
			case 'list':
				$url = mc_build_url( array('format'=>'calendar'), array() );		
				$format_toggle .= "<a href='$url'>".__('View as Grid','my-calendar')."</a>";			
			break;
			default:
				$url = mc_build_url( array('format'=>'list'), array() );	
				$format_toggle .= "<a href='$url'>".__('View as List','my-calendar')."</a>";
			break;
		}
		$format_toggle .= "</div>";
	} else {
		$format_toggle = '';
	}
	
	if ( isset( $_GET['mc_id'] ) && $format != 'mini' ) {
		$mc_id = (int) $_GET['mc_id'];
		$my_calendar_body .= mc_get_event( $id,'html' );
	} else {
	if ($category == "") {
		$category=null;
	}
    // First things first, make sure calendar is up to date
    check_my_calendar();
    // Deal with the week not starting on a monday
	$name_days = array(
		__('<abbr title="Sunday">Sun</abbr>','my-calendar'),
		__('<abbr title="Monday">Mon</abbr>','my-calendar'),
		__('<abbr title="Tuesday">Tues</abbr>','my-calendar'),
		__('<abbr title="Wednesday">Wed</abbr>','my-calendar'),
		__('<abbr title="Thursday">Thur</abbr>','my-calendar'),
		__('<abbr title="Friday">Fri</abbr>','my-calendar'),
		__('<abbr title="Saturday">Sat</abbr>','my-calendar')
		);
	$abbrevs = array( 'sun','mon','tues','wed','thur','fri','sat' );
	if ($format == "mini") {
		$name_days = array(
		__('<abbr title="Sunday">S</abbr>','my-calendar'),
		__('<abbr title="Monday">M</abbr>','my-calendar'),
		__('<abbr title="Tuesday">T</abbr>','my-calendar'),
		__('<abbr title="Wednesday">W</abbr>','my-calendar'),
		__('<abbr title="Thursday">T</abbr>','my-calendar'),
		__('<abbr title="Friday">F</abbr>','my-calendar'),
		__('<abbr title="Saturday">S</abbr>','my-calendar')
		);
	}
	$start_of_week = get_option('start_of_week');
	$start_of_week = ( get_option('mc_show_weekends') == 'true' )?$start_of_week:1;
	$start_of_week = ( $start_of_week==1||$start_of_week==0)?$start_of_week:0;
	if ( $start_of_week == '1' ) {
   			$first = array_shift($name_days);
			$afirst = array_shift($abbrevs);
			$name_days[] = $first;	
			$abbrevs[] = $afirst;
	}
     // Carry on with the script
	$offset = (60*60*get_option('gmt_offset'));
    // If we don't pass arguments we want a calendar that is relevant to today
	$c_m = 0;
	if ( isset($_GET['dy']) && $main_class == $cid ) {
		$c_day = (int) $_GET['dy'];
	} else {
		if ($time == 'week' ) {
			$dm = first_day_of_week();
			$c_day = $dm[0];
			$c_m = $dm[1];
		} else if ( $time == 'day' ) {
			$c_day = date("d",time()+($offset));
		} else {
			$c_day = 1;
		}
	}	
	if ( isset($_GET['month']) && $main_class == $cid  ) {
		$c_month = (int) $_GET['month'];
		if ( !isset($_GET['dy']) ) { $c_day = 1; }
	} else {
		$xnow = date('Y-m-d',time()+($offset));
		$c_month = ($c_m == 0)?date("m",time()+($offset)):date("m",strtotime( $xnow.' -1 month'));
	}

	if ( isset($_GET['yr']) && $main_class == $cid ) {
		$c_year = (int) $_GET['yr'];
	} else {
		$c_year = date("Y",time()+($offset));			
	}
    // Years get funny if we exceed 3000, so we use this check
    if ( !($c_year <= 3000 && $c_year >= 0)) {
		// No valid year causes the calendar to default to today
        $c_year = date("Y",time()+($offset));
        $c_month = date("m",time()+($offset));
        $c_day = date("d",time()+($offset));
    }
		$mc_print_url = mc_build_url( array( 'time'=>$time,'ltype'=>$ltype,'lvalue'=>$lvalue,'mcat'=>$category,'yr'=>$c_year,'month'=>$c_month,'dy'=>$c_day, 'cid'=>'print' ), array(), mc_feed_base() . 'my-calendar-print' );
		
	$anchor = (get_option('ajax_javascript') == '1' )?"#$id":'';	
	if ($shownav == 'yes') {
		$pLink = my_calendar_prev_link($c_year,$c_month,$c_day,$format,$time);
		$nLink = my_calendar_next_link($c_year,$c_month,$c_day,$format,$time);	
		$prevLink = mc_build_url( array( 'yr'=>$pLink['yr'],'month'=>$pLink['month'],'dy'=>$pLink['day'],'cid'=>$main_class ),array() );
		$nextLink = mc_build_url( array( 'yr'=>$nLink['yr'],'month'=>$nLink['month'],'dy'=>$nLink['day'],'cid'=>$main_class ),array() );
		$previous_link = apply_filters('mc_previous_link','		<li class="my-calendar-prev"><a class="prevMonth" href="' . $prevLink . $anchor .'" rel="nofollow">'.$pLink['label'].'</a></li>',$pLink);
		$next_link = apply_filters('mc_next_link','		<li class="my-calendar-next"><a class="nextMonth" href="' . $nextLink . $anchor .'" rel="nofollow">'.$nLink['label'].'</a></li>',$nLink);
		$mc_nav = '
<div class="my-calendar-nav">
	<ul>
		'.$previous_link.'
		'.$next_link.'
	</ul>
</div>';
	} else {
		$mc_nav = '';
	}
	$my_calendar_body .= "<div id=\"$id\" class=\"mc-main $format $time $main_class\">";
	if ( get_option( 'mc_show_print' ) == 'true' ) { $my_calendar_body .= "<p class='mc-print'><a href='$mc_print_url'>".__('Print View','my-calendar')."</a></p>"; }
	if ( $time == 'day' ) {
		$dayclass = strtolower(date_i18n('D',mktime (0,0,0,$c_month,$c_day,$c_year)));	
		$from = $to = "$c_year-$c_month-$c_day";
		$grabbed_events = my_calendar_grab_events($from,$to,$category,$ltype,$lvalue);
		$events_class = '';
		if ( !is_array($grabbed_events) || !count($grabbed_events) ) {
			$events_class = "no-events";
		} else {
			$class = '';
			foreach ( array_keys($grabbed_events) as $key ) {
				$an_event =& $grabbed_events[$key];	
				$author = ' author'.$an_event->event_author;
				if ( strpos ( $class, $author ) === false ) {
					$class .= $author;
				}
			}
			$events_class = "has-events$class";
		}
		$class = '';
		$dateclass = mc_dateclass( time()+$offset, mktime(0,0,0,$c_month,$c_day, $c_year ) );
		$my_calendar_body .= $mc_nav."\n"."<h3 class='mc-single".$class."'>".date_i18n( $date_format,strtotime("$c_year-$c_month-$c_day")).'</h3><div id="mc-day" class="'.$dayclass.' '.$dateclass.' '.$events_class.'">'."\n";
		$process_date = date_i18n("Y-m-d",strtotime("$c_year-$c_month-$c_day"));		
		if ( is_array($grabbed_events) && count($grabbed_events) > 0 ) {
			foreach ( array_keys($grabbed_events) as $key ) {
			$now =& $grabbed_events[$key];				
				$author = ' author'.$now->event_author;
				if ( strpos ( $class, $author ) === false ) {
					$class .= $author;
				}
			}
			$my_calendar_body .= my_calendar_draw_events($grabbed_events, $format, $process_date, $template);
		} else {
			$my_calendar_body .= __( 'No events scheduled for today!','my-calendar');
		}
		$my_calendar_body .= "</div>";
	} else {
		if ( !is_numeric($c_day) || $c_day == 0 ) { $c_day = date("d",time()+($offset)); }
		$days_in_month = date("t", mktime (0,0,0,$c_month,1,$c_year));
		$num_months = get_option('mc_show_months');
		
		if ( $time == 'month' && $c_day > $days_in_month ) {$c_day = $days_in_month;}
		
		$current_date = mktime(0,0,0,$c_month,$c_day,$c_year);
		$current_date_header = date_i18n('F Y',$current_date);
		$through_date = mktime(0,0,0,$c_month+($num_months-1),$c_day,$c_year);

		$current_month_header = ( date('Y',$current_date) == date('Y',$through_date) )?date_i18n('F',$current_date):date_i18n('F Y',$current_date);
		$through_month_header = date_i18n('F Y', $through_date);
		// Adjust the days of the week if week start is not Monday
			if ($time == 'week') {
				$first_weekday = $start_of_week;
			} else {
				if ( $start_of_week == 0 ) {
					$first_weekday = date("w",mktime(0,0,0,$c_month,1,$c_year));
				} else {
					$first_weekday = date("w",mktime(0,0,0,$c_month,1,$c_year));
					$first_weekday = ($first_weekday==0?6:$first_weekday-1);
				}
			}
			$and = __("and",'my-calendar');
			$category_label = ($category != "" && $category != "all")?str_replace("|"," $and ",$category) . ' ':'';
			// Add the calendar table and heading
			$caption_text = ' '.stripslashes( trim( get_option('mc_caption') ) );
			$mc_display_jump = ( $showjump != '' )?$showjump:get_option('mc_display_jump');
			if ( $mc_display_jump == 'yes' ) { $mc_display_jump = 'true'; }
				if ($format == "calendar" || $format == "mini" ) {
					$my_calendar_body .= '
			<div class="my-calendar-header">';
					// We want to know if we should display the date switcher
					if ( $time != 'week' && $time != 'day' ) {
						$my_calendar_body .= ( $mc_display_jump == 'true' )?mc_build_date_switcher( $format, $main_class ):'';
					}
					// The header of the calendar table and the links.
					$my_calendar_body .= "$mc_nav\n$format_toggle\n</div>";
					$my_calendar_body .= "\n<table class=\"my-calendar-table\">\n";
					$week_caption = stripslashes(get_option('mc_week_caption'));
					$caption_heading = ($time != 'week')?$current_date_header.$caption_text:$week_caption.$caption_text;
					$my_calendar_body .= "<caption class=\"my-calendar-$time\">".$caption_heading."</caption>\n";
				} else {
					// determine which header text to show depending on number of months displayed;
					if ( $time != 'week' && $time != 'day' ) {
						$list_heading = ($num_months <= 1)?__('Events in','my-calendar').' '.$current_date_header.$caption_text."\n":$current_month_header.'&ndash;'.$through_month_header.$caption_text;
					} else {
						$list_heading = stripslashes(get_option('mc_week_caption'));
					}
					$my_calendar_body .= "<h3 class=\"my-calendar-$time\">$list_heading</h3>\n";		
					$my_calendar_body .= '<div class="my-calendar-header">'; 
					// We want to know if we should display the date switcher
					if ( $time != 'week' && $time != 'day' ) {
						$my_calendar_body .= ( $mc_display_jump == 'true' )?mc_build_date_switcher( $format, $main_class ):'';
					}
					$my_calendar_body .= "$mc_nav\n$format_toggle\n</div>";	
				}
		// If in a calendar format, print the headings of the days of the week
	if ( $format == "calendar" || $format == "mini" ) {

		$my_calendar_body .= "<thead>\n<tr>\n";
		for ($i=0; $i<=6; $i++) {
			if ( $start_of_week == 0) {
				$class = ($i<6&&$i>0)?'day-heading':'weekend-heading';
			} else {
				$class = ($i<5)?'day-heading':'weekend-heading';
			}
			$dayclass = strtolower(strip_tags($abbrevs[$i]));
			if ( ( $class == 'weekend-heading' && get_option('mc_show_weekends') == 'true' ) || $class != 'weekend-heading' ) {
				$my_calendar_body .= "<th scope='col' class='$class $dayclass'>".$name_days[$i]."</th>\n";
			}
		}	
		$my_calendar_body .= "\n</tr>\n</thead>\n<tbody>";

		if ($time == 'week') {
			$firstday = date('j',mktime(0,0,0,$c_month,$c_day,$c_year));
			$lastday = $firstday + 6;
		} else {
			$firstday = 1;
			$lastday = $days_in_month;
		}
		$from = "$c_year-$c_month-$firstday";
		$to = "$c_year-$c_month-$lastday";

		// initiating variables
		$thisday = 0;	$useday = 1;	$inc_month = false;	$go = false;	$inc = 0;
			for ($i=$firstday; $i<=$lastday;) {
			$my_calendar_body .= "<tr>\n";
					if ($time == 'week') {
						$ii_start = $first_weekday;$ii_end = $first_weekday + 6;
					} else {
						$ii_start = 0;$ii_end = 6;
					}
					for ($ii=$ii_start; $ii<=$ii_end; $ii++) {
					// moved $process_date down here because needs to be updated daily, not weekly.
					$process_date = date_i18n('Y-m-d',mktime(0,0,0,$c_month,$thisday+1,$c_year));
						if ($ii==$first_weekday && $i==$firstday ) {
							$go = true;
						} else if ($thisday > $days_in_month ) {
							$go = false;
						}
						if ( empty( $thisday ) ) {
							$numdays = date('t',mktime(0,0,0,$c_month-1));
							$now = $numdays - ($first_weekday-($ii+1));
						}
						if ( $go ) {
						$addclass = "";
							if ($i > $days_in_month) {
								$addclass = " nextmonth";
								$thisday = $useday;
								if ($inc_month == false) {
									$c_year = ($c_month == 12)?$c_year+1:$c_year;
									$c_month = ($c_month == 12)?1:$c_month+1;
								} 
								$inc_month = true;
								$useday++;
							} else {
								$thisday = $i;
							}
							$class = '';
							$grabbed_events = my_calendar_grab_events($c_year,$c_month,$thisday,$category,$ltype,$lvalue); // JCD TODO: rewrite whole goddamn package.
							$events_class = '';
								if ( !is_array($grabbed_events) || !count($grabbed_events) ) {
									$events_class = "no-events$addclass";
									$element = 'span';
									$trigger = '';
									$close = 'span';
								} else {
									foreach ( $grabbed_events as $an_event ) {
										$author = ' author'.$an_event->event_author;
										if ( strpos ( $class, $author ) === false ) {
											$class .= $author;
										}
										$cat = ' mcat_'.sanitize_title($an_event->category_name);
										if ( strpos ( $class, $cat ) === false ) {
											$class .= $cat;
										}
									}			
									$events_class = "has-events$addclass$class";
									if ($format == 'mini') {
									 if ( get_option('mc_open_day_uri') == 'true' || get_option('mc_open_day_uri') == 'false' ) {
										$day_url = mc_build_url( array('yr'=>$c_year,'month'=>$c_month,'dy'=>$thisday), array('month','dy','yr','ltype','loc','mcat'), get_option( 'mc_day_uri' ) );
										$link = ( get_option('mc_day_uri') != '' )?$day_url:'#';
									} else {
										$atype = str_replace( 'anchor','',get_option('mc_open_day_uri') );
										$ad = str_pad( $thisday, 2, '0', STR_PAD_LEFT ); // need to match format in ID
										$am = str_pad( $c_month, 2, '0', STR_PAD_LEFT );
										$date_url = mc_build_url( array('yr'=>$c_year,'month'=>$c_month,'dy'=>$thisday), array('month','dy','yr','ltype','loc','mcat','cid'), get_option( 'mc_mini_uri' ) );	
										$link = ( get_option('mc_mini_uri') != '' ) ?$date_url.'#'.$atype.'-'.$c_year.'-'.$am.'-'.$ad:'#';
									}
										$element = 'a href="'.$link.'"';
										$close = 'a';
										$trigger = 'trigger';
									} else {
										$element = 'span';
										$trigger = '';
										$close = 'span';
									}
								}
								$dateclass = mc_dateclass( time()+$offset, mktime(0,0,0,$c_month,$thisday, $c_year ) );
								
							if ( $start_of_week == 0) {
								$class = ($ii<6&&$ii>0?"$trigger":" weekend $trigger");
								$is_weekend = ($ii<6&&$ii>0)?false:true;
								$i++;
							} else {
								$class = ($ii<5)?"$trigger":" weekend $trigger";
								$is_weekend = ($ii<5)?false:true;
								$i++;
							}
							$dayclass = strtolower(date_i18n('D',mktime (0,0,0,$c_month,$thisday,$c_year)));
							$week_format = (get_option('mc_week_format')=='')?'M j, \'y':get_option('mc_week_format');
							$week_date_format = date_i18n($week_format,strtotime( "$c_year-$c_month-$thisday" ) );				
							$thisday_heading = ($time == 'week')?"<small>$week_date_format</small>":$thisday;
							if ( ( $is_weekend && get_option('mc_show_weekends') == 'true' ) || !$is_weekend ) {
									$my_calendar_body .= "
	<td id='$format-$process_date' class='$dayclass $class $dateclass $events_class'>"."
		<$element class='mc-date $class'>$thisday_heading</$close>".
		my_calendar_draw_events($grabbed_events, $format, $process_date,$template)."
	</td>\n";
							}
					  } else {
						if ( !isset($now) ) { $now = 1; }
						if ( get_option('mc_show_weekends') != 'true' && date('N',strtotime(date('Y-m-d',mktime(0,0,0,$c_month,1,$c_year)))) < 6 ) {
							$process_date = date('Y-m-d',mktime(0,0,0,$c_month,$now,$c_year));
						} else {
							$process_date = date('Y-m-d',mktime(0,0,0,$c_month-1,$now,$c_year));						
						}
						$is_weekend = ( date('N',strtotime($process_date)) < 6 )?false:true;
						//$my_calendar_body .= date('N',$process_date);
						//if ( ( $is_weekend && get_option('mc_show_weekends') == 'true' ) || get_option('mc_show_weekends') != 'true' ) {
							if ( get_option('mc_show_weekends') == 'true' || ( get_option('mc_show_weekends') != 'true' && $inc < 5 ) ) {
								$dayclass = strtolower(date_i18n('D',strtotime($process_date)));
								$my_calendar_body .= "\n<td class='day-without-date $dayclass'>&nbsp;</td>\n";
							}
							$inc++;
						//}
					  }
					}
				$my_calendar_body .= "</tr>\n";
			}
		$my_calendar_body .= "\n</tbody>\n</table>";
	} else if ($format == "list") {
		if ( $id == 'jd-calendar' ) { $list_id = 'calendar-list'; } else { $list_id = $id; }
		$my_calendar_body .= "<ul id=\"$list_id\" class=\"mc-list\">";
		// show calendar as list
		$num_months = ($time == 'week')?1:get_option('mc_show_months');
		$num_events = 0;
		for ($m=0;$m<$num_months;$m++) {
			$add_month = ($m == 0)?0:1;
			$c_month = (int) $c_month + $add_month;
			if ($c_month > 12) {
				$c_month = $c_month - 12;
				$c_year = $c_year + 1;
			}
			$days_in_month = date("t", mktime (0,0,0,$c_month,1,$c_year));
			
				if ($time == 'week') {
					$firstday = date('j',mktime(0,0,0,$c_month,$c_day,$c_year));
					$lastday = $firstday + 6;
				} else {
					$firstday = 1;
					$lastday = $days_in_month;
				}
				$useday = 1;
				$inc_month = false;	
				$class = 'even';
			for ($i=$firstday; $i<=$lastday; $i++) {
					if ($i > $days_in_month) {
						$thisday = $useday;
						if ($inc_month == false) {
							$c_month = ($c_month == 12)?1:$c_month+1;
						} 
						$inc_month = true;
						$useday++;
					} else {
						$thisday = $i;
					}		
				$process_date = date_i18n('Y-m-d',mktime(0,0,0,$c_month,$thisday,$c_year));
				$grabbed_events = my_calendar_grab_events($c_year,$c_month,$thisday,$category,$ltype,$lvalue);// JCD TODO: rewrite whole goddamn package.
				if ( get_option('list_javascript') != 1) {
					$is_anchor = "<a href='#'>";
					$is_close_anchor = "</a>";
				} else {
					$is_anchor = $is_close_anchor = "";
				}
				$classes = mc_dateclass( time()+$offset, mktime(0,0,0,$c_month,$thisday, $c_year ) );
				$classes .= ( my_calendar_date_xcomp( $process_date, date('Y-m-d',time()+$offset) ) )?' past-date':'';
				$title = '';
				if ( is_array($grabbed_events) && count($grabbed_events) > 0 ) {
					usort( $grabbed_events, 'my_calendar_time_cmp' );
					$now = $grabbed_events[0];
					$count = count( $grabbed_events ) - 1;
					if ( $count == 0 ) { $cstate = ''; } else 
					if ( $count == 1 ) { 
						$cstate = sprintf(__(" and %d other event",'my-calendar'), $count); 
					} else {
						$cstate = sprintf(__(" and %d other events",'my-calendar'), $count); 
					}
					if ( get_option( 'mc_show_list_info' ) == 'true' ) {
						$title = ' - '.$is_anchor . stripcslashes($now->event_title).$cstate . $is_close_anchor;
					} else {
						$title = '';
					}
					$my_calendar_body .= "
					<li id='$format-$process_date' class='mc-events $class $classes'>
					<strong class=\"event-date\">$is_anchor".date_i18n($date_format,mktime(0,0,0,$c_month,$thisday,$c_year))."$is_close_anchor"."$title</strong>".my_calendar_draw_events($grabbed_events, $format, $process_date,$template)."
					</li>";
				$num_events++;
				$class = (my_calendar_is_odd($num_events))?"odd":"even";
				} 
			}
		}
		if ($num_events == 0) {
			$no_events = ( $content == '' )?__('There are no events scheduled during this period.','my-calendar'):$content;
			$my_calendar_body .= "<li class='no-events'>$no_events</li>";
		}
		$my_calendar_body .= "</ul>";
	} else {
		$my_calendar_body .= __("Unrecognized calendar format. Please use one of 'list','calendar', or 'mini'.",'my-calendar')." '<code>$format</code>.'";
	}	
	$my_calendar_body .= my_category_key( $showkey, $category );
			if ($format != 'mini') {
				$ical_m = (isset($_GET['month']))?(int) $_GET['month']:date('n');
				$ical_y = (isset($_GET['yr']))?(int) $_GET['yr']:date('Y');
				$my_calendar_body .= mc_rss_links($ical_y,$ical_m);
			}
		}
		$my_calendar_body .= "\n</div>";		
	}
    // The actual printing is done by the shortcode function.
	$my_calendar_body .= apply_filters('mc_after_calendar','',$args);
    return $my_calendar_body;
} */