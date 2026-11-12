const canvas = document.querySelector('[data-coffee-shader]');

if (canvas instanceof HTMLCanvasElement) {
    const gl = canvas.getContext('webgl', { alpha: false, antialias: false });

    if (gl) {
        const vertexShaderSource = `
            attribute vec2 a_position;
            varying vec2 v_texCoord;
            void main() {
                v_texCoord = a_position * 0.5 + 0.5;
                gl_Position = vec4(a_position, 0.0, 1.0);
            }
        `;
        const fragmentShaderSource = `
            precision mediump float;
            uniform float u_time;
            uniform vec2 u_resolution;
            varying vec2 v_texCoord;

            vec3 mod289(vec3 x) { return x - floor(x * (1.0 / 289.0)) * 289.0; }
            vec2 mod289(vec2 x) { return x - floor(x * (1.0 / 289.0)) * 289.0; }
            vec3 permute(vec3 x) { return mod289(((x * 34.0) + 1.0) * x); }

            float snoise(vec2 v) {
                const vec4 C = vec4(0.211324865405187, 0.366025403784439, -0.577350269189626, 0.024390243902439);
                vec2 i = floor(v + dot(v, C.yy));
                vec2 x0 = v - i + dot(i, C.xx);
                vec2 i1 = x0.x > x0.y ? vec2(1.0, 0.0) : vec2(0.0, 1.0);
                vec4 x12 = x0.xyxy + C.xxzz;
                x12.xy -= i1;
                i = mod289(i);
                vec3 p = permute(permute(i.y + vec3(0.0, i1.y, 1.0)) + i.x + vec3(0.0, i1.x, 1.0));
                vec3 m = max(0.5 - vec3(dot(x0, x0), dot(x12.xy, x12.xy), dot(x12.zw, x12.zw)), 0.0);
                m *= m;
                m *= m;
                vec3 x = 2.0 * fract(p * C.www) - 1.0;
                vec3 h = abs(x) - 0.5;
                vec3 ox = floor(x + 0.5);
                vec3 a0 = x - ox;
                m *= 1.79284291400159 - 0.85373472095314 * (a0 * a0 + h * h);
                vec3 g;
                g.x = a0.x * x0.x + h.x * x0.y;
                g.yz = a0.yz * x12.xz + h.yz * x12.yw;
                return 130.0 * dot(m, g);
            }

            void main() {
                vec2 uv = gl_FragCoord.xy / u_resolution.xy;
                float aspect = u_resolution.x / u_resolution.y;
                vec2 p = vec2(uv.x * aspect, uv.y);
                float t = u_time * 0.16;
                float n1 = snoise(p * 1.4 + vec2(t * 0.15, -t * 0.2));
                float n2 = snoise(p * 2.8 - vec2(n1 * 0.5, t * 0.25));
                float n3 = snoise(p * 4.6 + vec2(t * 0.08, n2 * 0.4));
                vec3 foam = vec3(0.992, 0.918, 0.855);
                vec3 crema = vec3(0.886, 0.741, 0.584);
                vec3 caramel = vec3(0.725, 0.463, 0.314);
                vec3 espresso = vec3(0.247, 0.133, 0.082);
                float blendVal = smoothstep(-0.7, 0.8, n1 + n2 * 0.5);
                vec3 color = mix(foam, crema, uv.y * 0.6 + blendVal * 0.4);
                color = mix(color, caramel, clamp(n2 * 0.75 + 0.2, 0.0, 1.0));
                color = mix(color, espresso, smoothstep(0.48, 0.98, n3 * 0.65 + uv.y * 0.35) * 0.45);
                float vignette = smoothstep(1.3, 0.25, length((uv - 0.5) * vec2(aspect, 1.0)));
                color *= mix(0.92, 1.03, vignette);
                gl_FragColor = vec4(color, 1.0);
            }
        `;

        const compileShader = (type, source) => {
            const shader = gl.createShader(type);

            if (!shader) {
                return null;
            }

            gl.shaderSource(shader, source);
            gl.compileShader(shader);

            if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
                console.error('Unable to compile the SipPly coffee shader:', gl.getShaderInfoLog(shader));
                gl.deleteShader(shader);

                return null;
            }

            return shader;
        };

        const vertexShader = compileShader(gl.VERTEX_SHADER, vertexShaderSource);
        const fragmentShader = compileShader(gl.FRAGMENT_SHADER, fragmentShaderSource);
        const program = gl.createProgram();

        if (vertexShader && fragmentShader && program) {
            gl.attachShader(program, vertexShader);
            gl.attachShader(program, fragmentShader);
            gl.linkProgram(program);

            if (gl.getProgramParameter(program, gl.LINK_STATUS)) {
                const buffer = gl.createBuffer();
                const position = gl.getAttribLocation(program, 'a_position');
                const timeUniform = gl.getUniformLocation(program, 'u_time');
                const resolutionUniform = gl.getUniformLocation(program, 'u_resolution');
                const resizeCanvas = () => {
                    const pixelRatio = Math.min(window.devicePixelRatio || 1, 1.5);
                    const width = Math.max(1, Math.floor(canvas.clientWidth * pixelRatio));
                    const height = Math.max(1, Math.floor(canvas.clientHeight * pixelRatio));

                    if (canvas.width !== width || canvas.height !== height) {
                        canvas.width = width;
                        canvas.height = height;
                    }
                };
                const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                let animationFrame = 0;

                if ('ResizeObserver' in window) {
                    new ResizeObserver(resizeCanvas).observe(canvas);
                } else {
                    window.addEventListener('resize', resizeCanvas);
                }
                resizeCanvas();
                gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
                gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 1, -1, -1, 1, 1, 1]), gl.STATIC_DRAW);
                gl.useProgram(program);
                gl.enableVertexAttribArray(position);
                gl.vertexAttribPointer(position, 2, gl.FLOAT, false, 0, 0);

                const draw = (time) => {
                    gl.viewport(0, 0, canvas.width, canvas.height);
                    gl.uniform1f(timeUniform, reducedMotion ? 0 : time * 0.001);
                    gl.uniform2f(resolutionUniform, canvas.width, canvas.height);
                    gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
                };
                const render = (time) => {
                    draw(time);

                    if (!reducedMotion && !document.hidden) {
                        animationFrame = window.requestAnimationFrame(render);
                    }
                };
                const handleVisibilityChange = () => {
                    window.cancelAnimationFrame(animationFrame);

                    if (document.hidden) {
                        return;
                    }

                    render(0);
                };

                draw(0);

                if (!reducedMotion) {
                    animationFrame = window.requestAnimationFrame(render);
                    document.addEventListener('visibilitychange', handleVisibilityChange);
                }
            } else {
                console.error('Unable to link the SipPly coffee shader:', gl.getProgramInfoLog(program));
            }
        }
    }
}
