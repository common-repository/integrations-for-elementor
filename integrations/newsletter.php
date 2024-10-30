<?php
/**
 * Newsletter plugin integration class.
 * 
 * @since 1.0.0
 * @package Elementor_Integrations
 * @author obiPlabon <obiplabon@gmail.com>
 */
namespace obiPlabon\Integrations;

use TNP;
use Elementor\Controls_Manager;
use \ElementorPro\Modules\Forms\Classes\Action_Base;

defined( 'ABSPATH' ) || exit;

class Newsletter extends Action_Base {

    /**
     * Integration name
     *
     * @return string
     */
    public function get_name() {
        return 'ei_newsletter';
    }

    /**
     * Integration label for dropdown
     *
     * @return string
     */
    public function get_label() {
        return esc_html__( 'Newsletter', 'elementor-integrations' );
    }

    public function register_settings_section( $widget ) {
        $widget->start_controls_section(
            '_ei_section_newsletter',
            [
                'label' => __( 'Newsletter', 'text-domain' ),
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );

        if ( ! defined( 'NEWSLETTER_VERSION' ) ) {
            $widget->add_control(
                $this->get_name() . '_missing_warning',
                [
                    'type' => Controls_Manager::RAW_HTML,
                    'raw' => '<a href="'.esc_url( admin_url( 'plugin-install.php?s=Newsletter&tab=search&type=term' ) ).'" target="_blank">Newsletter</a> plugin is either inactive or not installed. This action will not work without Newsletter plugin, make sure to install and activate the plugin.',
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
                ]
            );
        }

        $widget->add_control(
            'ei_newsletter_email_field',
            [
                'label' => __( 'Email Field ID', 'elementor-integrations' ),
                'type' => Controls_Manager::TEXT,
            ]
        );

        $list_ids = [];

        if ( defined( 'NEWSLETTER_VERSION' ) ) {
            $newsletter = \Newsletter::instance();
            $lists = $newsletter->get_lists();
            
            if ( ! empty( $lists ) ) {
                $list_ids = wp_list_pluck( $lists, 'name', 'id' );
            }
        }

        $widget->add_control(
            'ei_newsletter_lists',
            [
                'label' => __( 'Select Lists', 'elementor-integrations' ),
                'label_block' => true,
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $list_ids,
            ]
        );
        
        $widget->end_controls_section();
    }

    public function run( $record, $ajax_handler ) {
        $settings = $record->get( 'form_settings' );

		//  Make sure email field is there
		if ( empty( $settings['ei_newsletter_email_field'] ) ) {
			return;
        }

        $raw_fields = $record->get( 'fields' );

        // Normalize the Form Data
		$fields = [];
		foreach ( $raw_fields as $id => $field ) {
			$fields[ $id ] = $field['value'];
		}

        if ( empty( $fields[ $settings['ei_newsletter_email_field'] ] ) ) {
			return;
        }
        
        if ( ! is_email( $fields[ $settings['ei_newsletter_email_field'] ] ) ) {
            return;
        }

        $params = [
            'email' => $fields[ $settings['ei_newsletter_email_field'] ]
        ];

        if ( ! empty( $settings['ei_newsletter_lists'] ) && is_array( $settings['ei_newsletter_lists'] ) ) {
            $params['lists'] = $settings['ei_newsletter_lists'];
        }
        
        TNP::add_subscriber( $params );
    }

    public function on_export( $element ) {
        unset(
			$element['ei_newsletter_lists'],
			$element['ei_newsletter_email_field']
		);
    }
}
