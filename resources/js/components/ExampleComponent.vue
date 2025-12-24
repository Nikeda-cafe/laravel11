<script setup>
import { ref } from 'vue';

const count = ref(0);
</script>

<template>
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h2 class="card-title h5 mb-3">Laravel + Vue 3 + Bootstrap 5</h2>
            <p class="card-text">
                このコンポーネントは Vue 3 で描画され、Bootstrap のコンポーネントクラスを利用しています。
            </p>
            <div class="d-flex align-items-center gap-3">
                <span class="fw-semibold text-primary">Count: {{ count }}</span>
                <button class="btn btn-primary" type="button" @click="count++">
                    カウントアップ
                </button>
            </div>
        </div>
    </div>
</template>
