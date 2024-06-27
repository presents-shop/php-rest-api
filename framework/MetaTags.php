<?php

class MetaTags {
    private $tags = [];

    public function __construct(
            $title = META_TITLE,
            $description = META_DESCRIPTION,
            $keywords = META_KEYWORDS,
            $author = META_AUTHOR,
            $viewport = META_VIEWPORT,
            $googleSiteVerification = META_GOOGLE_VERIFICATION_CODE
        ) {
        if ($title !== null) {
            $this->setTitle($title);
        }
        if ($description !== null) {
            $this->setDescription($description);
        }
        if ($keywords !== null) {
            $this->setKeywords($keywords);
        }
        if ($author !== null) {
            $this->setAuthor($author);
        }
        if ($viewport !== null) {
            $this->setViewport($viewport);
        }
        if ($googleSiteVerification !== null) {
            $this->setGoogleSiteVerification($googleSiteVerification);
        }
    }

    public function setTitle($title) {
        $this->tags['title'] = "<title>{$title}</title>\n";
    }

    public function setDescription($description) {
        $this->tags['description'] = "<meta name=\"description\" content=\"{$description}\">\n";
    }

    public function setKeywords($keywords) {
        $this->tags['keywords'] = "<meta name=\"keywords\" content=\"{$keywords}\">\n";
    }

    public function setAuthor($author) {
        $this->tags['author'] = "<meta name=\"author\" content=\"{$author}\">\n";
    }

    public function setViewport($viewport) {
        $this->tags['viewport'] = "<meta name=\"viewport\" content=\"{$viewport}\">\n";
    }

    public function setGoogleSiteVerification($verificationCode) {
        $this->tags['google_site_verification'] = "<meta name=\"google-site-verification\" content=\"{$verificationCode}\">\n";
    }

    public function setCustomMetaTag($name, $content) {
        $this->tags[$name] = "<meta name=\"{$name}\" content=\"{$content}\">\n";
    }

    public function getTags() {
        return $this->tags;
    }
}

// Пример за използване
// $metaTags = new MetaTags(
//     "My Website Title",
//     "This is the description of my website.",
//     "PHP, Meta Tags, Google, SEO",
//     "John Doe",
//     "width=device-width, initial-scale=1",
//     "your_google_verification_code"
// );

// Добавяне на допълнителни мета тагове
// $metaTags->setCustomMetaTag("robots", "index, follow");
