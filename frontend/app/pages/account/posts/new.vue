<script setup>
definePageMeta({ middleware: 'auth' })
</script>

<template>
  <div class="page-bg" />
  <div class="page-wrapper">
    <NuxtLink to="/account/posts" class="back-link">Mes posts</NuxtLink>
  </div>
</template>