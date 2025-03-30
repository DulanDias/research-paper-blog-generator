<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Generate the full blog content using OpenAI API and return a parsed JSON array.
function rpbg_generate_blog_content( $pdf_path, $paper_link ) {
    // Extract text from the first page of the PDF.
    $first_page_text = rpbg_extract_first_page_text( $pdf_path );
    
    // Detailed default prompt instructing the model to output JSON.
    $default_prompt = "Using the following research paper excerpt:\n\n```\n%s\n```\n\nPlease generate an output in JSON format with the following keys:\n- \"title\": A catchy blog post title (max 10 words).\n- \"article\": A well-structured, human-like, engaging blog article that is SEO-optimized. Use proper HTML formatting for paragraphs and headings.\n- \"excerpt\": A short excerpt (around 40 words) summarizing the article.\n- \"tags\": A comma-separated list of relevant SEO-friendly tags.\n- \"socialMediaDescription\": A compelling, human-like description for sharing on social media.\n\nEnd the output with the following string: \"Read the full paper here: %s\".";
    
    // Retrieve the custom prompt from settings, defaulting if not set.
    $stored_prompt = get_option( 'rpbg_generation_prompt', $default_prompt );
    $prompt = sprintf( $stored_prompt, $first_page_text, $paper_link );
    
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
    
    $response = wp_remote_post( 'https://api.openai.com/v1/engines/davinci/completions', $args );
    if ( is_wp_error( $response ) ) {
        return false;
    }
    
    $body = wp_remote_retrieve_body( $response );
    $result = json_decode( $body, true );
    if ( empty( $result['choices'][0]['text'] ) ) {
        return false;
    }
    
    // Attempt to decode the model's output as JSON.
    $generated = json_decode( trim( $result['choices'][0]['text'] ), true );
    if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $generated ) ) {
        // Fallback: return false so the calling code can handle the error.
        return false;
    }
    
    return $generated;
}

// Generate a catchy topic/title using a simple algorithm (if not provided in JSON).
function rpbg_generate_topic( $content ) {
    $sentences = preg_split( '/(\.|\?|\!)(\s)/', $content, 2, PREG_SPLIT_DELIM_CAPTURE );
    return isset( $sentences[0] ) ? wp_trim_words( $sentences[0], 10, '...' ) : 'New Research Insight';
}

// Dummy function to extract text from PDF's first page.
function rpbg_extract_first_page_text( $pdf_path ) {
    return "Extracted text from the first page of the research paper.";
}

// Generate an excerpt from content (first 40 words)
function rpbg_generate_excerpt( $content, $word_limit = 40 ) {
    $words = explode( ' ', wp_strip_all_tags( $content ) );
    if ( count( $words ) > $word_limit ) {
        $excerpt = implode( ' ', array_slice( $words, 0, $word_limit ) ) . '...';
    } else {
        $excerpt = $content;
    }
    return $excerpt;
}

// Generate comma separated tags from content (default implementation)
function rpbg_generate_tags( $content ) {
    // For demonstration, return a default list.
    return "Artificial Intelligence, Research, Innovation, Technology";
}

// Get default category IDs for "Artificial Intelligence" and "Research"
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
