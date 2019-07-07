<?php
/**
 * The class file that contains method for saving dashboard data
 *
 * @since   1.0.0
 * @package PortfolioBackend\Rest\Rest_Callbacks
 */

namespace PortfolioBackend\Rest\Rest_Callbacks;

use PortfolioBackend\Core\Config;
use PortfolioBackend\Helpers\Object_Helper;
use PortfolioBackend\Helpers\General_Helper;

/**
 * Class Put_Portfolio_Page_Android
 */
class Put_Portfolio_Page_Android extends Config implements Rest_Callback {

  /**
   * Use trait inside class.
   */
  use Object_Helper;

  /**
   * Update portfolio general options data, updated through admin dashboard.
   *
   * This callback is triggered when a admin dashboard
   * goes to the @link https://API-URL/wp-json/portfolio-backend/v1/save-portfolio-topbar
   * endpoint.
   *
   * @api
   *
   * @throws \WP_Error Error if the token is missing or wrong or the password
   * is the same.
   * @param \WP_REST_Request $request Data got from enpoint url.
   * @return \WP_REST_Response|\WP_Error          Developer data array.
   *
   * @since 1.0.0
   */
  public function rest_callback( \WP_REST_Request $request ) {

    $body = \json_decode( $request->get_body(), true );

    $android_accent_color   = sanitize_text_field( General_Helper::get_array_value( 'androidAccentColor', $body ) );
    $android_description    = General_Helper::sanitize_html_input( General_Helper::get_array_value( 'androidDescription', $body ) );
    $android_animation_file = General_Helper::sanitize_media( General_Helper::get_array_value( 'androidAnimationFile', $body ) );

    $sanitized_projects = [];
    $projects           = General_Helper::get_array_value( 'androidProjects', $body );

    // sanitize all menu items object values.
    foreach ( $projects as $project ) {

      $sanitized_project = [];

      foreach ( $project as $key => $item ) {
        if ( $key !== 'title' && $key !== 'description' && $key !== 'link' ) {
          continue;
        }
        if ( $key === 'link' ) {
          $sanitized_project[ $key ] = esc_url_raw( $item );
          continue;
        }
        if ( $key === 'description' ) {
          $sanitized_project[ $key ] = General_Helper::sanitize_html_input( $item );
          continue;
        }

        $sanitized_project[ $key ] = sanitize_text_field( $item );
      }
      $sanitized_projects[] = $sanitized_project;
    }

    $sanitized_projects_string       = wp_json_encode( $sanitized_projects );

    $this->save_options( $android_animation_file, self::ANDROID_ANIMATION_FILE );
    $this->save_options( $android_accent_color, self::ANDROID_ACCENT_COLOR );
    $this->save_options( $android_description, self::ANDROID_DESCRIPTION );
    $this->save_options( $sanitized_projects_string, self::ANDROID_PROJECTS );

    return \rest_ensure_response( __( 'Android page saved', 'portfolio-backend' ) );
  }

}