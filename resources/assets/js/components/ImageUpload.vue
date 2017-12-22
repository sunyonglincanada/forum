<template>
    <input type="file" accept="image/*" @change="onChange">
</template>

<script>
    export default {

        methods: {

          onChange(e) {
              if(! e.target.files.length) return;

              let file = e.target.files[0];

              let reader = new FileReader();

              reader.readAsDataURL();

              reader.onload = e => {
                  let src = e.target.result;

                  this.$emit('loaded', {src, file});
              }
          }

        }
    }
</script>

<style>
    .alert-flash {
        position:fixed;
        right: 25px;
        bottom: 25px;
    }
</style>
