<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Generate the full blog content using the OpenAI API.
 *
 * - In PDF mode (if $pdf_path is not empty), the PDF file is sent as an attachment along with the prompt.
 *   The prompt instructs the model to convert the attached PDF to text as needed and generate a structured JSON output.
 * - In Citation mode (if $pdf_path is empty), the paper citation (link) is passed directly with the prompt.
 *
 * The output is expected to be a JSON string that, when decoded, contains the keys:
 *   "title", "article", "excerpt", "tags", "socialMediaDescription".
 *
 * Returns the parsed JSON output as an associative array, or false on failure.
 */
function rpbg_generate_blog_content( $pdf_path, $paper_link ) {
    // Use the selected model from settings.
    $model = get_option( 'rpbg_openai_model', 'davinci' );
    $api_key = get_option( 'rpbg_openai_api_key' );
    if ( empty( $api_key ) ) {
        return false;
    }
    
    if ( ! empty( $pdf_path ) ) {
        // PDF Mode: Build a prompt that instructs the model to use the attached PDF.
        $default_prompt_pdf = "Please use the attached PDF file as the source. Convert the PDF content to text as needed and generate an output in JSON format with the following keys:
- \"title\": A catchy blog post title (max 10 words).
- \"article\": A well-structured, human-like, engaging blog article that is SEO-optimized. Use proper HTML formatting for paragraphs and headings.
- \"excerpt\": A short excerpt (around 40 words) summarizing the article.
- \"tags\": A comma-separated list of relevant SEO-friendly tags.
- \"socialMediaDescription\": A compelling, human-like description for sharing on social media.
End the output with the following string: \"Read the full paper here: %s\".";
        
        $stored_prompt = get_option( 'rpbg_generation_prompt', $default_prompt_pdf );
        $prompt = sprintf( $stored_prompt, $paper_link );
        
        // Build a multipart/form-data POST request using cURL.
        $endpoint = 'https://api.openai.com/v1/engines/' . $model . '/completions';
        $pdfFile = curl_file_create( $pdf_path, mime_content_type( $pdf_path ), basename( $pdf_path ) );
        $postFields = array(
            'prompt'      => $prompt,
            'max_tokens'  => 800,
            'temperature' => 0.7,
            'file'        => $pdfFile,
        );
        
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $endpoint );
        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $postFields );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer $api_key",
        ) );
        
        $response = curl_exec( $ch );
        if ( curl_errno( $ch ) ) {
            error_log( 'cURL error: ' . curl_error( $ch ) );
            curl_close( $ch );
            return false;
        }
        curl_close( $ch );
        
    } else {
        // Citation Mode: Pass the citation directly.
        $default_prompt = "Using the following research paper citation:\n\n%s\n\nPlease generate an output in JSON format with the following keys:
- \"title\": A catchy blog post title (max 10 words).
- \"article\": A well-structured, human-like, engaging blog article that is SEO-optimized. Use proper HTML formatting for paragraphs and headings.
- \"excerpt\": A short excerpt (around 40 words) summarizing the article.
- \"tags\": A comma-separated list of relevant SEO-friendly tags.
- \"socialMediaDescription\": A compelling, human-like description for sharing on social media.
End the article with the following string: \"Read the full paper here: %s\".";
        $stored_prompt = get_option( 'rpbg_generation_prompt', $default_prompt );
        $prompt = sprintf( $stored_prompt, $paper_link, $paper_link );
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ),
            'body'    => json_encode( array(
                'prompt'      => $prompt,
                'max_tokens'  => 800,
                'temperature' => 0.7,
            ) ),
            'timeout' => 60,
        );
        
        $endpoint = 'https://api.openai.com/v1/engines/' . $model . '/completions';
        $response = wp_remote_post( $endpoint, $args );
        if ( is_wp_error( $response ) ) {
            return false;
        }
        $response = wp_remote_retrieve_body( $response );
    }
    
    $result = json_decode( $response, true );
    if ( empty( $result['choices'][0]['text'] ) ) {
        return false;
    }
    
    // Attempt to decode the output as JSON.
    $generated = json_decode( trim( $result['choices'][0]['text'] ), true );
    if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $generated ) ) {
        error_log( 'Error decoding generated JSON: ' . json_last_error_msg() );
        return false;
    }
    
    // Ensure all required keys are present.
    $required_keys = array('title', 'article', 'excerpt', 'tags', 'socialMediaDescription');
    foreach ( $required_keys as $key ) {
        if ( empty( $generated[$key] ) ) {
            error_log("Missing required key in generated output: " . $key);
            return false;
        }
    }
    
    return $generated;
}

/**
 * Fallback helper to generate a title (if needed).
 * (Not used because all keys must come from the prompt output.)
 */
function rpbg_generate_topic( $content ) {
    return ''; // Not used.
}

/**
 * Fallback helper to generate an excerpt (if needed).
 * (Not used because all keys must come from the prompt output.)
 */
function rpbg_generate_excerpt( $content, $word_limit = 40 ) {
    return ''; // Not used.
}

/**
 * Fallback helper to generate tags (if needed).
 * (Not used because all keys must come from the prompt output.)
 */
function rpbg_generate_tags( $content ) {
    return ''; // Not used.
}

/**
 * Get default category IDs for "Artificial Intelligence" and "Research".
 */
function rpbg_get_default_categories() {
    $cat1 = get_cat_ID( 'Artificial Intelligence' );
    if ( ! $cat1 ) {
        $cat1 = wp_insert_category( array( 'cat_name' => 'Artificial Intelligence' ) );
    }
    $cat2 = get_cat_ID( 'Research' );
    if ( ! $cat2 ) {
        $cat2 = wp_insert_category( array( 'cat_name' => 'Research' ) );
    }
    return array( $cat1, $cat2 );
}
?>
