<template>
  <Head :title="post.title" />
  <div class="news-article-container">
    <article>
      <header class="article-header">
        <h1 class="article-title">{{ post.title }}</h1>
        <div class="article-meta">
          <span v-if="post.category" class="meta-category">{{
            post.category
          }}</span>
          <span v-if="post.author" class="meta-author"
            >Bởi {{ post.author }}</span
          >
          <span class="meta-date">{{ post.created_at }}</span>
        </div>
        <div v-if="post.description" class="article-description">
          {{ post.description }}
        </div>
      </header>
      <section class="article-content" v-html="post.content"></section>
    </article>
  </div>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";

const props = defineProps({
  post: Object,
});
</script>

<style scoped>
.news-article-container {
  max-width: 900px;
  margin: 40px auto;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 16px rgba(0, 0, 0, 0.07);
  padding: 40px 32px;
  font-family: "Segoe UI", "Roboto", Arial, sans-serif;
}
.article-header {
  border-bottom: 1px solid #eee;
  margin-bottom: 24px;
  padding-bottom: 16px;
}
.article-title {
  font-size: 2.2rem;
  font-weight: bold;
  margin-bottom: 10px;
  color: #222;
}
.article-meta {
  font-size: 0.95rem;
  color: #888;
  margin-bottom: 12px;
  display: flex;
  gap: 18px;
  flex-wrap: wrap;
}
.meta-category {
  color: #1976d2;
  font-weight: 500;
}
.meta-author {
  color: #ea5455;
}
.meta-date {
  color: #6c757d;
}
.article-description {
  font-size: 1.1rem;
  color: #444;
  margin-bottom: 18px;
  font-style: italic;
}
.article-content {
  font-size: 1.15rem;
  line-height: 1.8;
  color: #222;
  margin-top: 10px;
}
.article-content h1,
.article-content h2,
.article-content h3,
.article-content h4,
.article-content h5,
.article-content h6 {
  font-weight: bold;
  margin: 18px 0 10px 0;
}
.article-content ul,
.article-content ol {
  margin-left: 24px;
  margin-bottom: 16px;
}
.article-content blockquote {
  border-left: 4px solid #1976d2;
  background: #f6f8fa;
  padding: 8px 16px;
  margin: 18px 0;
  font-style: italic;
  color: #555;
}
.article-content pre {
  background: #f5f5f5;
  padding: 12px;
  border-radius: 6px;
  margin: 18px 0;
  font-size: 0.98rem;
  overflow-x: auto;
}
.article-content code {
  background: #f5f5f5;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.98rem;
}
.article-content img {
  max-width: 100%;
  margin: 18px 0;
  border-radius: 6px;
}
</style>
