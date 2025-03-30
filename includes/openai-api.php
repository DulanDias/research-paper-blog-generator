<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Generate the full blog content using OpenAI API
function rpbg_generate_blog_content( $pdf_path, $paper_link ) {
    // Extract text from the first page of the PDF
    $first_page_text = rpbg_extract_first_page_text( $pdf_path );
    
    $prompt = "Using the following research paper excerpt:\n\n{$first_page_text}\n\nGenerate a human-like, engaging, SEO-optimized blog article. End the article with 'Read the full paper here: {$paper_link}'.";
    
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
            'max_tokens'  => 400,
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
    return isset( $result['choices'][0]['text'] ) ? trim( $result['choices'][0]['text'] ) : false;
}

// Generate a catchy topic/title using OpenAI API (or a simple algorithm)
function rpbg_generate_topic( $content ) {
    // For production, you could call OpenAI again or use heuristics.
    // For now, we extract the first sentence as the title.
    $sentences = preg_split( '/(\.|\?|\!)(\s)/', $content, 2, PREG_SPLIT_DELIM_CAPTURE );
    return isset( $sentences[0] ) ? wp_trim_words( $sentences[0], 10, '...' ) : 'New Research Insight';
}

// Dummy function to extract text from PDF's first page.
// Replace this with a proper PDF parser implementation.
function rpbg_extract_first_page_text( $pdf_path ) {
    return "Extracted text from the first page of the research paper.";
}
