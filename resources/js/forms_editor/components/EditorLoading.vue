<template>
    <div class="editor-loading">
        <i class="far fa-edit editor-loading__brand-icon"></i>
        <div class="editor-loading__title">申請フォームエディター</div>
        <div class="editor-loading__loading-wrap text-muted">
            <i class="fas fa-sync fa-spin editor-loading__loading"></i>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    .editor-loading {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        background: #fff;

        &__brand-icon {
            font-size: 3rem;
            display: block;
            margin-bottom: 1.5rem;
            animation: icon 1s ease both;

            @keyframes icon {
                from {
                    opacity: 0;
                    transform: translateY(-10rem);
                }

                75% {
                    opacity: .75;
                    transform: translateY(-1rem);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        }

        &__title {
            animation: title 1s ease both;
            font-size: 1.25rem;
            margin-bottom: 2rem;

            @keyframes title {
                from {
                    opacity: 0;
                }

                75% {
                    opacity: 0;
                    transform: translateY(2rem);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        }

        &__loading-wrap {
            animation: loading-wrap 2s ease both;

            @keyframes loading-wrap {
                from {
                    opacity: 0;
                }

                75% {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        }

        &__loading {
            font-size: 1.25rem;
        }
    }
</style>
