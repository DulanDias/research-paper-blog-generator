<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Generate the full blog content using the OpenAI API.
 * Reads the FULL PDF text.
 * Returns the parsed JSON output as an associative array.
 */
function rpbg_generate_blog_content( $pdf_path, $paper_link ) {
    // Extract full text from the PDF.
    $full_text = rpbg_extract_full_pdf_text( $pdf_path );
    
    // Detailed default prompt instructing the model to output JSON.
    $default_prompt = "Using the following research paper text:\n\n```\n%s\n```\n\nPlease generate an output in JSON format with the following keys:\n- \"title\": A catchy blog post title (max 10 words).\n- \"article\": A well-structured, human-like, engaging blog article that is SEO-optimized. Use proper HTML formatting for paragraphs and headings.\n- \"excerpt\": A short excerpt (around 40 words) summarizing the article.\n- \"tags\": A comma-separated list of relevant SEO-friendly tags.\n- \"socialMediaDescription\": A compelling, human-like description for sharing on social media.\n\nEnd the output with the following string: \"Read the full paper here: %s\".";
    
    $stored_prompt = get_option( 'rpbg_generation_prompt', $default_prompt );
    $prompt = sprintf( $stored_prompt, $full_text, $paper_link );
    
    // Use the model selected from settings.
    $model = get_option( 'rpbg_openai_model', 'davinci' );
    
    $api_key = get_option( 'rpbg_openai_api_key' );
    if ( empty( $api_key ) ) {
        return false;
    }
    
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
    
    // Use the selected model in the endpoint URL.
    $endpoint = 'https://api.openai.com/v1/engines/' . $model . '/completions';
    $response = wp_remote_post( $endpoint, $args );
    if ( is_wp_error( $response ) ) {
        return false;
    }
    
    $body = wp_remote_retrieve_body( $response );
    $result = json_decode( $body, true );
    if ( empty( $result['choices'][0]['text'] ) ) {
        return false;
    }
    
    // Attempt to decode the output as JSON.
    $generated = json_decode( trim( $result['choices'][0]['text'] ), true );
    if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $generated ) ) {
        return false;
    }
    
    return $generated;
}

/**
 * Extract full text from a PDF file.
 * This function attempts to use the 'pdftotext' utility to read the full content.
 * If it fails, it returns a dummy string.
 */
function rpbg_extract_full_pdf_text( $pdf_path ) {
    if ( file_exists( $pdf_path ) ) {
        $command = 'pdftotext ' . escapeshellarg( $pdf_path ) . ' -';
        $output = shell_exec( $command );
        if ( ! empty( $output ) ) {
            return trim( $output );
        }
    }
    return "Full text from the research paper could not be extracted.";
}

// The following helper functions remain largely unchanged.
function rpbg_generate_topic( $content ) {
    $sentences = preg_split( '/(\.|\?|\!)(\s)/', $content, 2, PREG_SPLIT_DELIM_CAPTURE );
    return isset( $sentences[0] ) ? wp_trim_words( $sentences[0], 10, '...' ) : 'New Research Insight';
}

function rpbg_generate_excerpt( $content, $word_limit = 40 ) {
    $words = explode( ' ', wp_strip_all_tags( $content ) );
    if ( count( $words ) > $word_limit ) {
        $excerpt = implode( ' ', array_slice( $words, 0, $word_limit ) ) . '...';
    } else {
        $excerpt = $content;
    }
    return $excerpt;
}

function rpbg_generate_tags( $content ) {
    return "Artificial Intelligence, Research, Innovation, Technology";
}

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
