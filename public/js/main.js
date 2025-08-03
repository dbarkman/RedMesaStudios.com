jQuery(document).ready(function($) {
    // Header scroll effect
    $(window).scroll(function() {
        if ($(this).scrollTop() > 100) {
            $('.site-header').addClass('scrolled');
        } else {
            $('.site-header').removeClass('scrolled');
        }
    });

    // Mobile menu toggle
    $('.menu-toggle').click(function() {
        $('.nav-menu').toggleClass('active');
        $(this).attr('aria-expanded', $('.nav-menu').hasClass('active'));
    });

    // Close mobile menu when clicking a link
    $('.nav-menu a').click(function() {
        $('.nav-menu').removeClass('active');
        $('.menu-toggle').attr('aria-expanded', 'false');
    });

    // Smooth scrolling for anchor links
    $('a[href*="#"]').not('[href="#"]').click(function(event) {
        if (
            location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') &&
            location.hostname == this.hostname
        ) {
            var target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
            
            if (target.length) {
                event.preventDefault();
                const distance = Math.abs($(window).scrollTop() - (target.offset().top - 80));
                // Faster speed: roughly 1000 pixels per second, with min/max constraints
                const duration = Math.min(Math.max(distance / 1.5, 300), 800); // Between 300ms and 800ms
                
                $('html, body').animate({
                    scrollTop: target.offset().top - 80 // Adjust for header height
                }, {
                    duration: duration,
                    // Force CSS-based animation for better performance on mobile
                    easing: 'linear',
                    step: function(now, fx) {
                        // Ensure smooth scrolling on mobile
                        if (fx.prop === 'scrollTop') {
                            fx.start = $(window).scrollTop();
                            fx.end = target.offset().top - 80;
                        }
                    }
                });
            }
        }
    });

    // About Us Carousel
    function initCarousel() {
        setTimeout(() => {
            const slides = $('.carousel-slide');
            if (slides.length === 0) return;

            let currentSlide = 0;
            slides.removeClass('active').css('display', 'none');
            slides.eq(0).addClass('active').css('display', 'block');

            function showSlide(index) {
                slides.eq(currentSlide).removeClass('active').fadeOut(500);
                currentSlide = index;
                slides.eq(currentSlide).addClass('active').fadeIn(500);
            }

            function nextSlide() {
                const next = (currentSlide + 1) % slides.length;
                showSlide(next);
            }

            setInterval(nextSlide, 3000);
        }, 1000);
    }

    // Initialize carousel
    initCarousel();

    // Contact form validation
    const form = $('#contact-form');
    if (form.length) {
        const submitButton = form.find('button[type="submit"]');
        const requiredFields = form.find('[required]');

        function validateForm() {
            let isValid = true;
            requiredFields.each(function() {
                if (!$(this).val().trim()) {
                    isValid = false;
                    return false; // break the loop
                }
            });
            submitButton.prop('disabled', !isValid);
        }

        requiredFields.on('input', validateForm);

        form.on('submit', function(e) {
            e.preventDefault();
            if (!submitButton.prop('disabled')) {
                const formContainer = $('.contact-form-container');
                submitButton.prop('disabled', true);
                const formWrapper = formContainer.find('.form-wrapper');

                $.ajax({
                    url: 'https://formspree.io/f/mbldvypw',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.ok) {
                            // Store original form content
                            const originalContent = formContainer.html();

                            // Fade out current content
                            formWrapper.fadeOut(500, function() {
                                // Show success message
                                formContainer.html(`
                                    <div class="success-message" style="display: none;">
                                        <h3>Message Sent Successfully!</h3>
                                        <p>Thank you for reaching out. We'll get back to you soon!</p>
                                    </div>
                                `);
                                
                                // Fade in success message
                                $('.success-message').fadeIn(500);

                                // After 3 seconds, transition back to form
                                setTimeout(() => {
                                    $('.success-message').fadeOut(500, function() {
                                        formContainer.html(originalContent);
                                        
                                        // Re-initialize form handlers
                                        const newForm = $('#contact-form');
                                        const newSubmitButton = newForm.find('button[type="submit"]');
                                        const newRequiredFields = newForm.find('[required]');
                                        
                                        function validateNewForm() {
                                            let isValid = true;
                                            newRequiredFields.each(function() {
                                                if (!$(this).val().trim()) {
                                                    isValid = false;
                                                    return false;
                                                }
                                            });
                                            newSubmitButton.prop('disabled', !isValid);
                                        }
                                        
                                        newRequiredFields.on('input', validateNewForm);
                                        newForm.on('submit', handleSubmit);
                                        newForm[0].reset();
                                        
                                        // Fade in the new form
                                        formContainer.find('.form-wrapper').fadeIn(500);
                                    });
                                }, 3000);
                            });
                        }
                    },
                    error: function() {
                        submitButton.prop('disabled', false);
                        alert('There was an error sending your message. Please try again.');
                    }
                });
            }
        });
    }
});

