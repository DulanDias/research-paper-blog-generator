# Research Paper Blog Generator

**Version:** 1.0  
**Author:** [Dulan Dias](https://dulandias.com/)

## Description

The Research Paper Blog Generator is a comprehensive WordPress plugin that automates the creation of engaging, SEO‑optimized blog posts from research papers. Whether you upload a PDF or provide a paper citation (link), the plugin leverages OpenAI's API to generate a fully structured blog post. The generated output is expected to be in JSON format—with keys for the title, article (HTML formatted), excerpt, comma‑separated tags, and social media description—and all these values are used directly.

In PDF mode, the PDF file is sent as an attachment with the prompt so that the model can convert it to text as needed. In citation mode, the provided paper citation is inserted into the prompt. The plugin also supports an intermediate draft workflow, scheduling options, category selection on upload, and stub functions for social media posting.

## Features

- **Research Paper Upload & Category Selection:**  
  - Upload a PDF file and/or provide a paper citation.
  - Optionally select one or more categories during upload (if none are selected, default categories "Artificial Intelligence" and "Research" are used).

- **Automatic Blog Generation via OpenAI API:**  
  - **PDF Mode:**  
    When a PDF is uploaded, it is sent as an attachment along with the prompt. The prompt instructs the model to convert the attached PDF to text and generate a structured JSON response.
  - **Citation Mode:**  
    If no PDF is provided, the paper citation (link) is passed directly within the prompt.
  - **Structured Output:**  
    The model returns a JSON object with the following keys (all extracted directly from the output):
    - `title`: A catchy blog post title.
    - `article`: A well-structured, engaging blog article (with proper HTML formatting).
    - `excerpt`: A short excerpt (around 40 words) summarizing the article.
    - `tags`: A comma‑separated list of SEO‑friendly tags.
    - `socialMediaDescription`: A compelling description for social media sharing.

- **Draft Workflow:**  
  - **Pending:** Newly uploaded papers start as pending.
  - **Draft:** Generate a draft blog post for review.
  - **Approved:** Approve the draft for publication.
  - **Published:** Publish immediately (or via the scheduler) once approved.

- **Social Media Integration:**  
  Stub functions are provided for posting the generated blog posts (using the generated social media description) to platforms like LinkedIn and Facebook.

- **Scheduling Options:**  
  Configure processing frequency (hourly, twice daily, or daily), along with the specific time and timezone for automatically publishing approved papers.

- **Featured Image Extraction:**  
  Uses PHP’s Imagick extension to extract the first page of the PDF, crop it as needed, and set it as the post’s featured image.

- **OpenAI Model & Prompt Customization:**  
  Choose the OpenAI model (e.g., `davinci`, `curie`, etc.) and customize the prompt via the settings page. The default prompt is carefully constructed to instruct the model to return a structured JSON output.

## Installation

1. Download the plugin ZIP file.
2. In your WordPress dashboard, navigate to **Plugins > Add New**.
3. Click **Upload Plugin** and select the ZIP file.
4. Install and activate the plugin.
5. Configure the OpenAI API settings (API key, prompt, and model) via **RP Blog Generator > OpenAI API Settings**.
6. Configure scheduling options via **RP Blog Generator > Scheduler**.
7. Upload research papers via **RP Blog Generator > Upload Paper**.

## Usage

- **Uploading Papers:**  
  - Use the Upload Paper page to add research papers.
  - Optionally select one or more categories during upload.
  
- **Draft Workflow:**  
  - Click **Generate Draft** on a pending paper to create a draft blog post.
  - Review draft details (including title, excerpt, tags, social media description, and preview link) via the “View Draft” icon in the Research Papers list.
  - Approve the draft to enable publishing.

- **Immediate & Scheduled Publishing:**  
  - Once approved, you can click **Publish Now** to publish the draft immediately.
  - Alternatively, approved papers will be automatically published according to your configured scheduler settings.

- **Social Media:**  
  - The plugin uses the generated social media description when posting to connected platforms. (Note: Social media posting functions are provided as stubs and need to be implemented with the appropriate API integrations.)

## Configuration

- **OpenAI API Settings:**  
  - Set your OpenAI API key.
  - Customize the generation prompt.  
    *Default prompt (for PDF mode):*  
    > "Please use the attached PDF file as the source. Convert the PDF content to text as needed and generate an output in JSON format with the following keys:  
    > - 'title': A catchy blog post title (max 10 words).  
    > - 'article': A well-structured, human-like, engaging blog article that is SEO-optimized. Use proper HTML formatting for paragraphs and headings.  
    > - 'excerpt': A short excerpt (around 40 words) summarizing the article.  
    > - 'tags': A comma-separated list of relevant SEO-friendly tags.  
    > - 'socialMediaDescription': A compelling, human-like description for sharing on social media.  
    > End the output with the following string: 'Read the full paper here: %s'"
  - Select the OpenAI model (e.g., `davinci`, `curie`, etc.).

- **Scheduler Settings:**  
  - Configure the processing frequency, the time of day (24-hour format), and the timezone in the Scheduler settings.

## Requirements

- PHP with the Imagick extension enabled.
- The ability to upload files via PHP (ensure your server permissions are correctly set).
- A valid OpenAI API key.
- WordPress 5.0 or higher.

## Support

For support, please visit [dulandias.com](https://dulandias.com).

## Changelog

### 1.0
- Initial release of the Research Paper Blog Generator plugin.
- Supports blog generation via both PDF mode (sending the PDF as an attachment) and citation mode.
- Structured JSON output extraction for title, article, excerpt, tags, and social media description.
- Draft workflow: pending, draft, approved, and published stages.
- Category selection during upload.
- Featured image extraction via PHP’s Imagick.
- OpenAI model selection and prompt customization.
- Scheduler options for automated publishing.
- Social media integration stubs.
- Posts authored by Dulan Dias.

---

Enjoy automating your blog content creation with the Research Paper Blog Generator!
