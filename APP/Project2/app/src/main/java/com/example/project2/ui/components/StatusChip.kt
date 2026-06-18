package com.example.project2.ui.components

import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.unit.dp
import com.example.project2.ui.theme.GlassBorder
import com.example.project2.ui.theme.PrimaryPurple
import com.example.project2.ui.theme.TextPrimary

@Composable
fun StatusChip(
    text: String,
    modifier: Modifier = Modifier,
    selected: Boolean = true,
    onClick: (() -> Unit)? = null
) {
    Surface(
        modifier = modifier,
        onClick = onClick ?: {},
        enabled = onClick != null,
        color = if (selected) PrimaryPurple.copy(alpha = 0.28f) else Color.White.copy(alpha = 0.06f),
        contentColor = TextPrimary,
        shape = RoundedCornerShape(999.dp),
        border = BorderStroke(1.dp, if (selected) PrimaryPurple.copy(alpha = 0.52f) else GlassBorder)
    ) {
        Text(
            text = text,
            modifier = Modifier.padding(horizontal = 12.dp, vertical = 7.dp),
            style = MaterialTheme.typography.labelMedium
        )
    }
}
