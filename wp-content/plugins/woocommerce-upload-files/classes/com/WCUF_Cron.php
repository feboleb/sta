<?php 
class WCUF_Cron
{
	public function __construct()
	{
		add_action( 'wp_loaded', array(&$this,'schedule_events') );	//wp event fiered only when accessin frontend
		add_action( 'wcuf_delete_order_empty_directories', array(&$this, 'delete_order_empty_directories' ));
		add_action( 'cron_schedules', array(&$this, 'cron_schedules' ));
	}
	function cron_schedules()
	{
		/* if(!isset($schedules["wcmc_15_minutes"]))
		{
			$schedules["wcuf_15_minutes"] = array(
			'interval' => 15*60, //15 minutes
			'display' => __('Once every 15 Minutes'));
		} */
		if(!isset($schedules["wcuf_60_minutes"]))
		{
			$schedules["wcuf_60_minutes"] = array(
			'interval' => 60*60, 
			'display' => __('Once every 60 Minutes'));
		}
		return $schedules;
	}
	function schedule_events() 
	{
		
		//wcuf_var_dump(wp_next_scheduled( 'wcuf_delete_order_empty_directories' ));
		if ( !wp_next_scheduled( 'wcuf_delete_order_empty_directories' ) ) 
		{
			wp_schedule_event( time(), "wcuf_60_minutes", 'wcuf_delete_order_empty_directories' ); //seconds
		}
		
		//wp_clear_scheduled_hook( 'wcuf_delete_order_empty_directories' );
		
	}
	function delete_order_empty_directories()
	{
		global $wcuf_file_model;
		
		$wcuf_file_model->start_delete_empty_order_directories();
	}
}
?>