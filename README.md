# Research Paper Blog Generator

**Version:** 1.0  
**Author:** [Dulan Dias](https://dulandias.com)

## Description
The Research Paper Blog Generator is a WordPress plugin that automates the generation of engaging, SEO‑optimized blog posts from uploaded research papers (PDF format). The plugin integrates with the OpenAI API to summarize and generate content from research papers and automatically publishes the generated blog post along with a featured image. It supports both scheduled processing and immediate publishing and also includes stub functions for social media posting.

## Features
- **Research Paper Upload:** Upload research papers (PDF) and/or provide a research paper link.
- **Automatic Blog Generation:** Uses OpenAI API to generate human-like, engaging blog posts.
- **SEO Optimization:** Automatically assigns default categories ("Artificial Intelligence" and "Research"), generates an excerpt (first 40 words), and sets comma‑separated tags.
- **Customizable Prompt:** Configure the prompt used for generating blog articles via the settings page. The prompt supports `%s` placeholders for the paper excerpt and the paper link.
- **Featured Image Extraction:** Extracts the first page of the PDF, crops the top portion, and sets it as the featured image.
- **Immediate Publishing:** Use the "Generate & Publish Now" button to generate and publish a blog post immediately.
- **Scheduled Publishing:** Automatically processes pending research papers based on your configured schedule (WP‑Cron).
- **Social Media Integration:** Stub functions for posting generated blog posts to LinkedIn and Facebook.
- **Author:** All blog posts are published under the author "Dulan Dias".

## Installation
1. Download the plugin ZIP file.
2. In your WordPress dashboard, navigate to **Plugins > Add New**.
3. Click **Upload Plugin** and select the ZIP file.
4. Install and activate the plugin.
5. Configure the OpenAI API settings and custom generation prompt via **RP Blog Generator > OpenAI API Settings**.
6. Upload research papers via **RP Blog Generator > Upload Paper**.

## Usage
- **Uploading Papers:**  
  Use the Upload Paper page to add research papers. The papers will appear in the Research Papers list.
- **Immediate Publishing:**  
  On the Research Papers list page, click the "Generate & Publish Now" button to generate and publish a blog post immediately.
- **Scheduled Publishing:**  
  The plugin processes pending papers automatically based on your configured schedule.

## Configuration
- **OpenAI API Settings:**  
  Set your OpenAI API key and customize the prompt for generating blog articles.  
  *Prompt Format:*  
  Use `%s` as placeholders for the research paper excerpt and the paper link.
- **Scheduler:**  
  Configure the processing frequency (hourly, twice daily, or daily) in the Scheduler settings.

## Requirements
- PHP with the Imagick extension enabled.
- A valid OpenAI API key.
- WordPress 5.0 or higher.

## Support
For support, please visit [dulandias.com](https://dulandias.com).

## Changelog
### 1.0
- Initial release of the Research Paper Blog Generator plugin.
- Automatic generation of SEO‑optimized blog posts from research papers.
- Featured image extraction, default categories, excerpt, and tags.
- Immediate publishing option and scheduled processing.
- Customizable OpenAI generation prompt.
- Posts authored by Dulan Dias.

---

Enjoy automating your blog content creation with the Research Paper Blog Generator!
