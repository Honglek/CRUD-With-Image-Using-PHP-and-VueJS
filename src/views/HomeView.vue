<template>
  <div class="home">
    <student />
  </div>
</template>

<script>
import student from "@/components/student.vue";

export default {
  name: "HomeView",
  components: {
    student,
  },
};
</script>
